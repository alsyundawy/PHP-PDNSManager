<?php
declare(strict_types=1);
namespace App\Services\PowerDNS;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Exceptions\PowerApiException;

class PowerDNSClient implements PowerDNSClientInterface
{
    private GuzzleClient $client;
    private Config $config;
    private Logger $logger;
    private string $serverId = 'localhost';
    public function __construct(Config $config, Logger $logger, ?GuzzleClient $client = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $baseUri = rtrim((string) ($config->get('powerdns.api_url') ?? 'http://127.0.0.1:8081'), '/');
        $apiKey = (string) ($config->get('powerdns.api_key') ?? '');
        $this->client = $client ?? new GuzzleClient([
            'base_uri' => $baseUri . '/api/v1/',
            'headers' => [
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => (float) $config->get('powerdns.timeout', 30.0),
            'verify' => (bool) $config->get('powerdns.verify_ssl', true),
            'http_errors' => false,
        ]);
        $this->serverId = (string) ($config->get('powerdns.server_id') ?? 'localhost');
    }
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }
    public function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }
    public function put(string $uri, array $data = []): array
    {
        return $this->request('PUT', $uri, ['json' => $data]);
    }
    public function patch(string $uri, array $data = []): array
    {
        return $this->request('PATCH', $uri, ['json' => $data]);
    }
    public function delete(string $uri): void
    {
        $this->request('DELETE', $uri);
    }
    public function getServerId(): string
    {
        return $this->serverId;
    }
    private function request(string $method, string $uri, array $options = []): array
    {
        if (!str_starts_with($uri, '/')) {
            $uri = '/servers/' . $this->serverId . '/' . ltrim($uri, '/');
        }
        $startTime = microtime(true);
        try {
            $response = $this->client->request($method, $uri, $options);
            $body = (string) $response->getBody();
            $data = json_decode($body, true) ?? [];
            $this->logRequest($method, $uri, $response->getStatusCode(), microtime(true) - $startTime);
            if ($response->getStatusCode() >= 400) {
                $error = $data['error'] ?? $data['message'] ?? 'PowerDNS API error';
                $code = $response->getStatusCode();
                throw new PowerApiException("PowerDNS API error: {$error}", $code);
            }
            return $data;
        } catch (GuzzleException $e) {
            $this->logger->error('PowerDNS API request failed', [
                'method' => $method,
                'uri' => $uri,
                'error' => $e->getMessage(),
            ]);
            throw new PowerApiException('PowerDNS API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
    private function logRequest(string $method, string $uri, int $status, float $duration): void
    {
        $this->logger->debug('PowerDNS API request', [
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'duration' => round($duration * 1000, 2) . 'ms',
        ]);
    }
}
