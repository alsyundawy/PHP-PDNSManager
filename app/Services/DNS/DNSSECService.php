<?php
declare(strict_types=1);
namespace App\Services\DNS;
use App\Services\PowerDNS\Resource\CryptokeyResource;
use App\Core\Logger;

class DNSSECService
{
    private CryptokeyResource $cryptokeyResource;
    private Logger $logger;
    public function __construct(CryptokeyResource $cryptokeyResource, Logger $logger)
    {
        $this->cryptokeyResource = $cryptokeyResource;
        $this->logger = $logger;
    }
    public function getKeys(string $zoneId): array
    {
        return $this->cryptokeyResource->getAll($zoneId);
    }
    public function createKey(string $zoneId, array $data): array
    {
        $key = $this->cryptokeyResource->create($zoneId, $data);
        $this->logger->info('DNSSEC key created', ['zone' => $zoneId, 'key_id' => $key['id'] ?? 'unknown']);
        return $key;
    }
    public function activateKey(string $zoneId, string $keyId): array
    {
        $key = $this->cryptokeyResource->update($zoneId, $keyId, ['active' => true]);
        $this->logger->info('DNSSEC key activated', ['zone' => $zoneId, 'key_id' => $keyId]);
        return $key;
    }
    public function deactivateKey(string $zoneId, string $keyId): array
    {
        $key = $this->cryptokeyResource->update($zoneId, $keyId, ['active' => false]);
        $this->logger->info('DNSSEC key deactivated', ['zone' => $zoneId, 'key_id' => $keyId]);
        return $key;
    }
    public function deleteKey(string $zoneId, string $keyId): void
    {
        $this->cryptokeyResource->delete($zoneId, $keyId);
        $this->logger->info('DNSSEC key deleted', ['zone' => $zoneId, 'key_id' => $keyId]);
    }
}
