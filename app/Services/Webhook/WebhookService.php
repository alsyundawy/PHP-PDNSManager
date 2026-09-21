<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Webhook;
use GuzzleHttp\Client as GuzzleClient;
use PDO;

class WebhookService
{
    private Database $db;
    private Logger $logger;
    private ?GuzzleClient $httpClient = null;

    public function __construct(Database $db, Logger $logger, ?GuzzleClient $httpClient = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    /**
     * @return Webhook[]
     */
    public function getAllWebhooks(): array
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM webhooks ORDER BY name ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'hydrateWebhook'], $rows);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting webhooks: ' . $e->getMessage());
            return [];
        }
    }

    public function getWebhookById(int $id): ?Webhook
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM webhooks WHERE id = ? LIMIT 1', [$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
            return $this->hydrateWebhook($row);
        } catch (\Throwable $e) {
            $this->logger->error('Error getting webhook by id: ' . $e->getMessage());
            return null;
        }
    }

    public function createWebhook(array $data): ?Webhook
    {
        $name = trim((string) ($data['name'] ?? ''));
        $url = trim((string) ($data['url'] ?? ''));
        $secret = isset($data['secret']) && $data['secret'] !== '' ? (string) $data['secret'] : bin2hex(random_bytes(16));
        $events = is_array($data['events'] ?? null) ? json_encode($data['events']) : (string) ($data['events'] ?? '["*"]');
        $isActive = !empty($data['is_active']) ? 1 : 0;

        $this->db->execute(
            'INSERT INTO webhooks (name, url, secret, events, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [$name, $url, $secret, $events, $isActive]
        );

        $id = (int) $this->db->lastInsertId();
        return $this->getWebhookById($id);
    }

    public function updateWebhook(int $id, array $data): bool
    {
        $webhook = $this->getWebhookById($id);
        if ($webhook === null) {
            return false;
        }

        $name = isset($data['name']) ? trim((string) $data['name']) : $webhook->name;
        $url = isset($data['url']) ? trim((string) $data['url']) : $webhook->url;
        $secret = isset($data['secret']) ? (string) $data['secret'] : $webhook->secret;
        $events = isset($data['events']) ? (is_array($data['events']) ? json_encode($data['events']) : (string) $data['events']) : json_encode($webhook->events);
        $isActive = isset($data['is_active']) ? (!empty($data['is_active']) ? 1 : 0) : ($webhook->is_active ? 1 : 0);

        $this->db->execute(
            'UPDATE webhooks SET name = ?, url = ?, secret = ?, events = ?, is_active = ?, updated_at = datetime("now") WHERE id = ?',
            [$name, $url, $secret, $events, $isActive, $id]
        );

        return true;
    }

    public function deleteWebhook(int $id): bool
    {
        $this->db->execute('DELETE FROM webhooks WHERE id = ?', [$id]);
        return true;
    }

    /**
     * Dispatch an event payload to all subscribed webhooks.
     */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = $this->getAllWebhooks();
        $client = $this->httpClient ?? new GuzzleClient(['timeout' => 5.0, 'connect_timeout' => 2.0]);

        $body = [
            'event' => $event,
            'timestamp' => date('c'),
            'data' => $payload,
        ];
        $jsonPayload = (string) json_encode($body);

        foreach ($webhooks as $webhook) {
            if (!$webhook->is_active) {
                continue;
            }

            // Check if subscribed to event
            if (!in_array('*', $webhook->events, true) && !in_array($event, $webhook->events, true)) {
                continue;
            }

            // Calculate HMAC-SHA256 signature
            $signature = '';
            if ($webhook->secret !== null && $webhook->secret !== '') {
                $signature = hash_hmac('sha256', $jsonPayload, $webhook->secret);
            }

            try {
                $headers = [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PHP-PDNSManager-Webhook/2.0',
                    'X-PDNS-Event' => $event,
                ];
                if ($signature !== '') {
                    $headers['X-PDNS-Signature'] = 'sha256=' . $signature;
                }

                $client->post($webhook->url, [
                    'headers' => $headers,
                    'body' => $jsonPayload,
                ]);

                $this->db->execute('UPDATE webhooks SET last_triggered_at = datetime("now") WHERE id = ?', [$webhook->id]);
            } catch (\Throwable $e) {
                $this->logger->warning("Webhook delivery failed to {$webhook->url}: " . $e->getMessage());
            }
        }
    }

    private function hydrateWebhook(array $row): Webhook
    {
        $webhook = new Webhook();
        $webhook->id = (int) ($row['id'] ?? 0);
        $webhook->name = (string) ($row['name'] ?? '');
        $webhook->url = (string) ($row['url'] ?? '');
        $webhook->secret = isset($row['secret']) ? (string) $row['secret'] : null;

        $rawEvents = $row['events'] ?? '[]';
        $decoded = is_string($rawEvents) ? json_decode($rawEvents, true) : [];
        $webhook->events = is_array($decoded) ? $decoded : ['*'];

        $webhook->is_active = (bool) ($row['is_active'] ?? true);
        $webhook->last_triggered_at = isset($row['last_triggered_at']) ? (string) $row['last_triggered_at'] : null;
        $webhook->created_at = isset($row['created_at']) ? (string) $row['created_at'] : null;
        $webhook->updated_at = isset($row['updated_at']) ? (string) $row['updated_at'] : null;
        return $webhook;
    }
}
