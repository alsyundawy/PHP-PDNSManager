<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Services\PowerDNS\Resource\ZoneResource;
use App\Services\PowerDNS\Resource\RecordResource;
use App\ValueObjects\IpAddress;
use App\Core\Logger;
use InvalidArgumentException;

class ReverseZoneService
{
    private ZoneResource $zoneResource;
    private RecordResource $recordResource;
    private Logger $logger;

    public function __construct(
        ZoneResource $zoneResource,
        RecordResource $recordResource,
        Logger $logger
    ) {
        $this->zoneResource = $zoneResource;
        $this->recordResource = $recordResource;
        $this->logger = $logger;
    }

    /**
     * Create an IPv4 reverse zone for a /24 subnet (e.g. 192.0.2.0/24 -> 2.0.192.in-addr.arpa.).
     */
    public function createIpv4ReverseZone(string $subnetCidr, array $nameservers = []): array
    {
        $parts = explode('/', trim($subnetCidr));
        $ipStr = $parts[0];
        $prefix = (int) ($parts[1] ?? 24);

        if ($prefix !== 24 && $prefix !== 16 && $prefix !== 8) {
            throw new InvalidArgumentException('IPv4 reverse zones must align with byte boundaries (/24, /16, or /8).');
        }

        $ip = new IpAddress($ipStr);
        if (!$ip->isV4()) {
            throw new InvalidArgumentException('Target address is not a valid IPv4 address.');
        }

        $zoneName = $ip->toReverseZoneName($prefix);

        $payload = [
            'name' => $zoneName,
            'kind' => 'Native',
            'nameservers' => !empty($nameservers) ? $nameservers : ['ns1.' . $zoneName],
        ];

        $zone = $this->zoneResource->create($payload);
        $this->logger->info('IPv4 Reverse Zone created', ['zone' => $zoneName, 'cidr' => $subnetCidr]);

        return $zone;
    }

    /**
     * Create an IPv6 reverse zone for a /64 or /48 prefix (e.g. 2001:db8::/64 -> 0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.).
     */
    public function createIpv6ReverseZone(string $prefixCidr, array $nameservers = []): array
    {
        $parts = explode('/', trim($prefixCidr));
        $ipStr = $parts[0];
        $prefix = (int) ($parts[1] ?? 64);

        if ($prefix % 4 !== 0 || $prefix < 16 || $prefix > 128) {
            throw new InvalidArgumentException('IPv6 reverse zones must align with 4-bit nibble boundaries (e.g. /64, /48, /32).');
        }

        $ip = new IpAddress($ipStr);
        if (!$ip->isV6()) {
            throw new InvalidArgumentException('Target address is not a valid IPv6 address.');
        }

        $zoneName = $ip->toReverseZoneName($prefix);

        $payload = [
            'name' => $zoneName,
            'kind' => 'Native',
            'nameservers' => !empty($nameservers) ? $nameservers : ['ns1.' . $zoneName],
        ];

        $zone = $this->zoneResource->create($payload);
        $this->logger->info('IPv6 Reverse Zone created', ['zone' => $zoneName, 'cidr' => $prefixCidr]);

        return $zone;
    }

    /**
     * Locate existing reverse zone matching an IP address in PowerDNS.
     */
    public function findReverseZoneForIp(string $ipStr): ?string
    {
        try {
            $ip = new IpAddress($ipStr);
        } catch (InvalidArgumentException) {
            return null;
        }

        $candidateZones = [];
        if ($ip->isV4()) {
            $candidateZones = [
                $ip->toReverseZoneName(24),
                $ip->toReverseZoneName(16),
                $ip->toReverseZoneName(8),
            ];
        } else {
            $candidateZones = [
                $ip->toReverseZoneName(64),
                $ip->toReverseZoneName(48),
                $ip->toReverseZoneName(32),
            ];
        }

        $allZones = $this->zoneResource->getAll();
        $zoneNames = array_column($allZones, 'name');

        foreach ($candidateZones as $candidate) {
            if (in_array($candidate, $zoneNames, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Synchronize PTR record automatically when an A or AAAA record is created, updated, or deleted.
     */
    public function syncAutoPtr(string $ipStr, string $fqdn, string $action = 'REPLACE'): bool
    {
        try {
            $ip = new IpAddress($ipStr);
        } catch (InvalidArgumentException) {
            return false;
        }

        $reverseZone = $this->findReverseZoneForIp($ipStr);
        if ($reverseZone === null) {
            $this->logger->info('Auto-PTR skipped: No matching reverse zone found in PowerDNS', ['ip' => $ipStr]);
            return false;
        }

        $ptrName = $ip->toReversePtrName();
        $targetFqdn = strtolower(rtrim($fqdn, '.')) . '.';

        if ($action === 'DELETE') {
            $rrsets = [
                [
                    'name' => $ptrName,
                    'type' => 'PTR',
                    'changetype' => 'DELETE',
                    'records' => [],
                ],
            ];
        } else {
            $rrsets = [
                [
                    'name' => $ptrName,
                    'type' => 'PTR',
                    'ttl' => 3600,
                    'changetype' => 'REPLACE',
                    'records' => [
                        [
                            'content' => $targetFqdn,
                            'disabled' => false,
                        ],
                    ],
                ],
            ];
        }

        $this->recordResource->bulkUpdate($reverseZone, $rrsets);
        $this->logger->info('Auto-PTR synchronized', [
            'zone' => $reverseZone,
            'ptr' => $ptrName,
            'target' => $targetFqdn,
            'action' => $action,
        ]);

        return true;
    }
}
