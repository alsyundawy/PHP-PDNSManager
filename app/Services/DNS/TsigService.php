<?php

declare(strict_types=1);

namespace App\Services\DNS;

use App\Services\PowerDNS\Resource\TsigResource;
use App\Core\Logger;
use InvalidArgumentException;

class TsigService
{
    public const SUPPORTED_ALGORITHMS = [
        'hmac-sha256',
        'hmac-sha512',
        'hmac-sha384',
        'hmac-sha224',
        'hmac-sha1',
        'hmac-md5',
    ];

    private TsigResource $tsigResource;
    private Logger $logger;

    public function __construct(TsigResource $tsigResource, Logger $logger)
    {
        $this->tsigResource = $tsigResource;
        $this->logger = $logger;
    }

    public function getAllKeys(): array
    {
        return $this->tsigResource->getAll();
    }

    public function getKey(string $id): array
    {
        return $this->tsigResource->get($id);
    }

    public function createKey(string $name, string $algorithm = 'hmac-sha256', ?string $secret = null): array
    {
        $cleanName = trim($name);
        if ($cleanName === '') {
            throw new InvalidArgumentException('TSIG key name cannot be empty.');
        }

        $algoLower = strtolower(trim($algorithm));
        if (!in_array($algoLower, self::SUPPORTED_ALGORITHMS, true)) {
            throw new InvalidArgumentException("Unsupported TSIG algorithm: {$algorithm}");
        }

        $keySecret = $secret !== null && trim($secret) !== ''
            ? trim($secret)
            : base64_encode(random_bytes(32));

        $payload = [
            'name' => $cleanName,
            'algorithm' => $algoLower,
            'key' => $keySecret,
        ];

        $result = $this->tsigResource->create($payload);
        $this->logger->info('TSIG key created', [
            'name' => $cleanName,
            'algorithm' => $algoLower,
        ]);

        return $result;
    }

    public function deleteKey(string $id): void
    {
        $this->tsigResource->delete($id);
        $this->logger->info('TSIG key deleted', ['id' => $id]);
    }

    public function getSupportedAlgorithms(): array
    {
        return self::SUPPORTED_ALGORITHMS;
    }
}
