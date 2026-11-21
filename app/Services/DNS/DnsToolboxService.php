<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Core\Logger;
use App\ValueObjects\IpAddress;
use InvalidArgumentException;

class DnsToolboxService
{
    private const MAX_SPF_LOOKUPS = 10;
    private const RBL_PROVIDERS = [
        'Spamhaus ZEN' => 'zen.spamhaus.org',
        'Barracuda RBL' => 'b.barracudacentral.org',
        'SpamCop BL' => 'bl.spamcop.net',
    ];

    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Validate an SPF record against RFC 7208 standards and the 10 DNS lookup limit.
     */
    public function validateSpf(string $domain, ?string $spfRecord = null): array
    {
        $targetDomain = strtolower(rtrim(trim($domain), '.'));
        $this->logger->info('Validating SPF record', ['domain' => $targetDomain]);
        $record = $spfRecord !== null ? trim($spfRecord) : $this->queryTxtRecord($targetDomain, 'v=spf1');

        if ($record === null || $record === '') {
            return [
                'valid' => false,
                'record' => null,
                'lookup_count' => 0,
                'warnings' => ['No SPF (v=spf1) record found for domain.'],
                'errors' => ['Domain is missing SPF email authentication record.'],
            ];
        }

        $tokens = preg_split('/\s+/', $record) ?: [];
        $lookupCount = 0;
        $warnings = [];
        $errors = [];

        if (($tokens[0] ?? '') !== 'v=spf1') {
            $errors[] = 'Record does not start with valid SPF version identifier (v=spf1).';
        }

        $allFound = false;
        foreach (array_slice($tokens, 1) as $token) {
            $cleanToken = strtolower($token);

            if (preg_match('/^[+\-~?]?(?:include:|redirect=)/', $cleanToken)) {
                $lookupCount++;
            } elseif (preg_match('/^[+\-~?]?(?:a(?::|\/|$)|mx(?::|\/|$)|ptr(?::|\/|$)|exists:)/', $cleanToken)) {
                $lookupCount++;
                if (str_starts_with($cleanToken, 'ptr') || str_starts_with($cleanToken, '+ptr')) {
                    $warnings[] = 'The "ptr" mechanism is deprecated by RFC 7208 section 5.5 and should not be used.';
                }
            } elseif (preg_match('/^[+\-~?]?all$/', $cleanToken)) {
                $allFound = true;
                if ($cleanToken === '+all' || $cleanToken === 'all') {
                    $warnings[] = 'CRITICAL: The "+all" mechanism allows ANY server in the world to spoof emails for your domain!';
                }
            }
        }

        if (!$allFound) {
            $warnings[] = 'Record lacks a terminating "all" mechanism (e.g. "~all" or "-all").';
        }

        if ($lookupCount > self::MAX_SPF_LOOKUPS) {
            $errors[] = sprintf(
                'RFC 7208 limit exceeded: Record requires %d DNS lookups (maximum permitted is %d). Mail servers may return PermError.',
                $lookupCount,
                self::MAX_SPF_LOOKUPS
            );
        }

        return [
            'valid' => empty($errors),
            'record' => $record,
            'lookup_count' => $lookupCount,
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    /**
     * Build an RFC 7208 compliant SPF string from parameters.
     */
    public function buildSpf(array $params): string
    {
        $parts = ['v=spf1'];

        if (!empty($params['a'])) {
            $parts[] = 'a';
        }
        if (!empty($params['mx'])) {
            $parts[] = 'mx';
        }

        foreach ((array) ($params['ip4'] ?? []) as $ip) {
            $cleanIp = trim((string) $ip);
            if ($cleanIp !== '') {
                $parts[] = 'ip4:' . $cleanIp;
            }
        }

        foreach ((array) ($params['ip6'] ?? []) as $ip) {
            $cleanIp = trim((string) $ip);
            if ($cleanIp !== '') {
                $parts[] = 'ip6:' . $cleanIp;
            }
        }

        foreach ((array) ($params['include'] ?? []) as $inc) {
            $cleanInc = trim((string) $inc);
            if ($cleanInc !== '') {
                $parts[] = 'include:' . $cleanInc;
            }
        }

        $qualifier = trim((string) ($params['qualifier'] ?? '~all'));
        $parts[] = in_array($qualifier, ['-all', '~all', '?all'], true) ? $qualifier : '~all';

        return implode(' ', $parts);
    }

    /**
     * Inspect a DKIM public key record at selector._domainkey.domain.
     */
    public function inspectDkim(string $domain, string $selector): array
    {
        $cleanDomain = strtolower(rtrim(trim($domain), '.'));
        $cleanSelector = trim($selector);

        if ($cleanSelector === '') {
            throw new InvalidArgumentException('DKIM selector cannot be empty.');
        }

        $dkimHost = "{$cleanSelector}._domainkey.{$cleanDomain}";
        $record = $this->queryTxtRecord($dkimHost, 'v=DKIM1');

        if ($record === null) {
            return [
                'valid' => false,
                'host' => $dkimHost,
                'record' => null,
                'key_length' => 0,
                'warnings' => [],
                'errors' => ["No DKIM public key record found at {$dkimHost}"],
            ];
        }

        $warnings = [];
        $keyLength = 0;

        if (preg_match('/p=([a-zA-Z0-9+\/]+={0,2})/', $record, $matches)) {
            $pubKeyBase64 = $matches[1];
            $decoded = base64_decode($pubKeyBase64, true);
            if ($decoded !== false) {
                $keyLength = strlen($decoded) * 8;
                if ($keyLength < 2048) {
                    $warnings[] = "DKIM public key is {$keyLength} bits. Modern security standards mandate at least 2048 bits.";
                }
            }
        } else {
            $warnings[] = 'Record does not appear to contain a valid public key (p= tag missing or empty).';
        }

        return [
            'valid' => true,
            'host' => $dkimHost,
            'record' => $record,
            'key_length' => $keyLength,
            'warnings' => $warnings,
            'errors' => [],
        ];
    }

    /**
     * Validate an RFC 7489 DMARC record at _dmarc.domain.
     */
    public function validateDmarc(string $domain, ?string $dmarcRecord = null): array
    {
        $targetDomain = strtolower(rtrim(trim($domain), '.'));
        $record = $dmarcRecord !== null
            ? trim($dmarcRecord)
            : $this->queryTxtRecord("_dmarc.{$targetDomain}", 'v=DMARC1');

        if ($record === null || $record === '') {
            return [
                'valid' => false,
                'record' => null,
                'warnings' => ['No DMARC record found at _dmarc.' . $targetDomain],
                'errors' => ['Domain is unprotected against email spoofing (missing DMARC).'],
            ];
        }

        $warnings = [];
        $errors = [];

        if (!str_starts_with($record, 'v=DMARC1')) {
            $errors[] = 'DMARC record must begin with "v=DMARC1".';
        }

        if (!preg_match('/(?:^|;\s*)p=(none|quarantine|reject)(?:;|$)/i', $record, $m)) {
            $errors[] = 'Missing or invalid mandatory policy tag "p=" (must be none, quarantine, or reject).';
        } elseif (strtolower($m[1]) === 'none') {
            $warnings[] = 'Policy is set to "p=none" (monitoring only). Spoofed emails will still be delivered.';
        }

        if (!preg_match('/rua=/i', $record)) {
            $warnings[] = 'No aggregate reporting URI specified (rua= tag is missing).';
        }

        return [
            'valid' => empty($errors),
            'record' => $record,
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    /**
     * Build an RFC 7489 DMARC policy record.
     */
    public function buildDmarc(array $params): string
    {
        $policy = in_array($params['p'] ?? '', ['none', 'quarantine', 'reject'], true)
            ? $params['p']
            : 'quarantine';

        $parts = ["v=DMARC1", "p={$policy}"];

        if (!empty($params['sp']) && in_array($params['sp'], ['none', 'quarantine', 'reject'], true)) {
            $parts[] = "sp={$params['sp']}";
        }

        if (!empty($params['rua'])) {
            $rua = trim((string) $params['rua']);
            $parts[] = str_starts_with($rua, 'mailto:') ? "rua={$rua}" : "rua=mailto:{$rua}";
        }

        if (!empty($params['ruf'])) {
            $ruf = trim((string) $params['ruf']);
            $parts[] = str_starts_with($ruf, 'mailto:') ? "ruf={$ruf}" : "ruf=mailto:{$ruf}";
        }

        $pct = (int) ($params['pct'] ?? 100);
        if ($pct < 100 && $pct >= 0) {
            $parts[] = "pct={$pct}";
        }

        $aspf = $params['aspf'] ?? 'r';
        $parts[] = in_array($aspf, ['r', 's'], true) ? "aspf={$aspf}" : 'aspf=r';

        $adkim = $params['adkim'] ?? 'r';
        $parts[] = in_array($adkim, ['r', 's'], true) ? "adkim={$adkim}" : 'adkim=r';

        return implode('; ', $parts) . ';';
    }

    /**
     * Check IP reputation across top DNS Blacklists (RBL/DNSBL).
     */
    public function checkRbl(string $ipStr): array
    {
        try {
            $ip = new IpAddress($ipStr);
        } catch (InvalidArgumentException) {
            return ['error' => 'Invalid IP address provided for RBL check.'];
        }

        if (!$ip->isV4()) {
            return ['error' => 'Standard DNSBL queries only support IPv4 addresses.'];
        }

        $octets = explode('.', $ip->toString());
        $revIp = implode('.', array_reverse($octets));

        $results = [];
        foreach (self::RBL_PROVIDERS as $name => $host) {
            $queryHost = "{$revIp}.{$host}";
            $records = @dns_get_record($queryHost, DNS_A);
            $listed = !empty($records);

            $results[] = [
                'provider' => $name,
                'host' => $host,
                'listed' => $listed,
                'return_code' => $listed ? ($records[0]['ip'] ?? '127.0.0.2') : null,
                'status' => $listed ? 'LISTED (Blacklisted)' : 'CLEAN (Not Listed)',
            ];
        }

        return [
            'ip' => $ipStr,
            'is_clean' => !in_array(true, array_column($results, 'listed'), true),
            'providers' => $results,
        ];
    }

    /**
     * Internal helper to query TXT records matching a prefix.
     */
    private function queryTxtRecord(string $host, string $prefix): ?string
    {
        $records = @dns_get_record($host, DNS_TXT);
        if (empty($records)) {
            return null;
        }

        foreach ($records as $r) {
            $txt = $r['txt'] ?? ($r['entries'][0] ?? '');
            if (str_starts_with(trim($txt), $prefix)) {
                return trim($txt);
            }
        }

        return null;
    }
}
