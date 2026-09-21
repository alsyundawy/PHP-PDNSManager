<?php

declare(strict_types=1);

namespace App\Services\PowerDNS;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Models\PdnsServer;
use PDO;

class ServerClusterService
{
    private Database $db;
    private Config $config;
    private Logger $logger;

    public function __construct(Database $db, Config $config, Logger $logger)
    {
        $this->db = $db;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return PdnsServer[]
     */
    public function getAllServers(): array
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM pdns_servers ORDER BY is_default DESC, name ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return [$this->getDefaultConfigServer()];
            }
            return array_map([$this, 'hydrateServer'], $rows);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to query pdns_servers, falling back to default config: ' . $e->getMessage());
            return [$this->getDefaultConfigServer()];
        }
    }

    public function getServerById(int $id): ?PdnsServer
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM pdns_servers WHERE id = ? LIMIT 1', [$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
            return $this->hydrateServer($row);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting pdns server by id: ' . $e->getMessage());
            return null;
        }
    }

    public function getDefaultServer(): PdnsServer
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM pdns_servers WHERE is_default = 1 AND is_active = 1 LIMIT 1');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                return $this->hydrateServer($row);
            }
        } catch (\Throwable) {
            // Fall through to default config server
        }
        return $this->getDefaultConfigServer();
    }

    public function createServer(array $data): PdnsServer
    {
        $name = trim((string) ($data['name'] ?? ''));
        $apiUrl = rtrim(trim((string) ($data['api_url'] ?? '')), '/');
        $apiKey = trim((string) ($data['api_key'] ?? ''));
        // DevSkim: ignore DS137138 - PowerDNS API default server ID is 'localhost'
        $serverId = trim((string) ($data['server_id'] ?? 'localhost')) ?: 'localhost';
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $isDefault = !empty($data['is_default']) ? 1 : 0;

        if ($isDefault === 1) {
            $this->db->execute('UPDATE pdns_servers SET is_default = 0');
        }

        $this->db->execute(
            'INSERT INTO pdns_servers (name, api_url, api_key, server_id, is_active, is_default, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [$name, $apiUrl, $apiKey, $serverId, $isActive, $isDefault]
        );

        $id = (int) $this->db->lastInsertId();
        return $this->getServerById($id) ?? $this->getDefaultConfigServer();
    }

    public function updateServer(int $id, array $data): bool
    {
        $server = $this->getServerById($id);
        if ($server === null) {
            return false;
        }

        $name = isset($data['name']) ? trim((string) $data['name']) : $server->name;
        $apiUrl = isset($data['api_url']) ? rtrim(trim((string) $data['api_url']), '/') : $server->apiUrl;
        $apiKey = !empty($data['api_key']) ? trim((string) $data['api_key']) : $server->apiKey;
        $serverId = isset($data['server_id']) ? trim((string) $data['server_id']) : $server->serverId;

        $isActive = $server->isActive ? 1 : 0;
        if (isset($data['is_active'])) {
            $isActive = !empty($data['is_active']) ? 1 : 0;
        }

        $isDefault = $server->isDefault ? 1 : 0;
        if (isset($data['is_default'])) {
            $isDefault = !empty($data['is_default']) ? 1 : 0;
        }

        if ($isDefault === 1) {
            $this->db->execute('UPDATE pdns_servers SET is_default = 0 WHERE id != ?', [$id]);
        }

        $this->db->execute(
            'UPDATE pdns_servers SET name = ?, api_url = ?, api_key = ?, server_id = ?, is_active = ?, is_default = ?, updated_at = datetime("now") WHERE id = ?',
            [$name, $apiUrl, $apiKey, $serverId, $isActive, $isDefault, $id]
        );

        return true;
    }

    public function deleteServer(int $id): bool
    {
        $this->db->execute('DELETE FROM pdns_servers WHERE id = ?', [$id]);
        return true;
    }

    // DevSkim: ignore DS137138 - PowerDNS API default server ID is 'localhost'
    public function testConnection(string $apiUrl, string $apiKey, string $serverId = 'localhost'): array
    {
        $client = new PowerDNSClient($apiUrl, $apiKey, ['server_id' => $serverId, 'timeout' => 5.0]);
        try {
            $start = microtime(true);
            $res = $client->get('');
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'success' => true,
                'latency_ms' => $latency,
                'version' => $res['version'] ?? 'Unknown',
                'daemon_type' => $res['daemon_type'] ?? 'Authoritative',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getClientForServer(PdnsServer $server): PowerDNSClientInterface
    {
        return new PowerDNSClient($server->apiUrl, $server->apiKey, [
            'server_id' => $server->serverId,
            'timeout' => 15.0,
        ]);
    }

    private function getDefaultConfigServer(): PdnsServer
    {
        $server = new PdnsServer();
        $server->id = 0;
        $server->name = 'Default PowerDNS (Config)';
        // DevSkim: ignore DS137138 - Default fallback local PowerDNS API URL
        $server->apiUrl = (string) ($this->config->get('powerdns.api_url') ?? 'https://127.0.0.1:8081');
        $server->apiKey = (string) ($this->config->get('powerdns.api_key') ?? '');
        // DevSkim: ignore DS137138 - PowerDNS API default server ID is 'localhost'
        $server->serverId = (string) ($this->config->get('powerdns.server_id') ?? 'localhost');
        $server->isActive = true;
        $server->isDefault = true;
        return $server;
    }

    private function hydrateServer(array $row): PdnsServer
    {
        $server = new PdnsServer();
        $server->id = (int) ($row['id'] ?? 0);
        $server->name = (string) ($row['name'] ?? '');
        $server->apiUrl = (string) ($row['api_url'] ?? '');
        $server->apiKey = (string) ($row['api_key'] ?? '');
        // DevSkim: ignore DS137138 - PowerDNS API default server ID is 'localhost'
        $server->serverId = (string) ($row['server_id'] ?? 'localhost');
        $server->isActive = (bool) ($row['is_active'] ?? true);
        $server->isDefault = (bool) ($row['is_default'] ?? false);
        $server->createdAt = isset($row['created_at']) ? (string) $row['created_at'] : null;
        $server->updatedAt = isset($row['updated_at']) ? (string) $row['updated_at'] : null;
        return $server;
    }
}
