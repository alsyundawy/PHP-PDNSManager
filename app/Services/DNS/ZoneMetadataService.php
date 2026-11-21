<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Services\PowerDNS\Resource\ZoneResource;
use App\Core\Logger;
use InvalidArgumentException;

class ZoneMetadataService
{
    public const SUPPORTED_KINDS = [
        'SOA-EDIT' => 'Determines how the serial number of the zone is updated during changes',
        'SOA-EDIT-API' => 'Controls whether and how PowerDNS API updates serial upon record changes',
        'ALLOW-AXFR-FROM' => 'Permits zone transfers (AXFR) only from specified IP/CIDR ranges',
        'AXFR-SOURCE' => 'Source IP address to bind to when sending zone transfers to slaves',
        'NSEC3PARAM' => 'Hash parameters for NSEC3 records when DNSSEC is active',
        'NSEC3NARROW' => 'Enables narrow NSEC3 mode (white lies)',
        'TSIG-ALLOW-AXFR' => 'Names of TSIG keys permitted to perform AXFR transfers',
        'FORWARD-DNSUPDATE' => 'Forwards dynamic DNS updates (RFC 2136) to masters',
        'NOTIFY-DNSUPDATE' => 'Sends NOTIFY packets upon dynamic DNS updates',
    ];

    private ZoneResource $zoneResource;
    private Logger $logger;

    public function __construct(ZoneResource $zoneResource, Logger $logger)
    {
        $this->zoneResource = $zoneResource;
        $this->logger = $logger;
    }

    public function getAllMetadata(string $zoneId): array
    {
        return $this->zoneResource->getMetadata($zoneId);
    }

    public function getMetadataKind(string $zoneId, string $kind): array
    {
        return $this->zoneResource->getMetadataKind($zoneId, $kind);
    }

    public function setMetadataKind(string $zoneId, string $kind, array $values): void
    {
        $kindUpper = strtoupper(trim($kind));
        if (!array_key_exists($kindUpper, self::SUPPORTED_KINDS)) {
            throw new InvalidArgumentException("Unsupported metadata kind: {$kind}");
        }

        $cleanValues = array_values(array_filter(array_map('trim', $values), fn($v) => $v !== ''));
        $this->zoneResource->setMetadata($zoneId, $kindUpper, $cleanValues);

        $this->logger->info('Zone metadata updated', [
            'zone' => $zoneId,
            'kind' => $kindUpper,
            'values' => $cleanValues,
        ]);
    }

    public function deleteMetadataKind(string $zoneId, string $kind): void
    {
        $kindUpper = strtoupper(trim($kind));
        $this->zoneResource->deleteMetadata($zoneId, $kindUpper);

        $this->logger->info('Zone metadata deleted', [
            'zone' => $zoneId,
            'kind' => $kindUpper,
        ]);
    }

    public function getSupportedKinds(): array
    {
        return self::SUPPORTED_KINDS;
    }
}
