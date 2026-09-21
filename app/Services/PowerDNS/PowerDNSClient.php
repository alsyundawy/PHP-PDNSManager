<?php

declare(strict_types=1);

namespace App\Services\PowerDNS;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use App\Core\Exceptions\PowerApiException;

/**
 * BUGFIX: PHPStan "Instantiated class GuzzleHttp not found" — the original
 * used bare 'GuzzleHttp\\ Client' without a use import, causing static analysis
 * failures. Added fully qualified use aliases.
 * SECURITY: Added SSL verification config, timeout, and connect_timeout.
 */
class PowerDNSClient implements PowerDNSClientInterface
{
    private const CONTENT_TYPE_JSON = 'application/json';

    private GuzzleClient $client;
    private string $baseUrl;
    private string $apiKey;
    private string $serverId;

    public function __construct(
        string|\App\Core\Config $baseUrlOrConfig,
        string|\App\Core\Logger $apiKeyOrLogger = '',
        array|GuzzleClient $optionsOrClient = []
    ) {
        if ($baseUrlOrConfig instanceof \App\Core\Config) {
            $config = $baseUrlOrConfig;
            $this->serverId = (string) ($config->get('powerdns.server_id') ?? 'localhost');
            $apiUrl = (string) ($config->get('powerdns.api_url') ?? 'http://127.0.0.1:8081');
            $this->baseUrl = rtrim($apiUrl, '/') . '/api/v1/servers/' . $this->serverId . '/';
            $this->apiKey = (string) ($config->get('powerdns.api_key') ?? '');
            if ($optionsOrClient instanceof GuzzleClient) {
                $this->client = $optionsOrClient;
            } else {
                $this->client = new GuzzleClient([
                    'base_uri' => $this->baseUrl,
                    'timeout' => (float) ($config->get('powerdns.timeout', 30.0)),
                    'verify' => (bool) ($config->get('powerdns.verify_ssl', true)),
                    'headers' => [
                        'X-API-Key' => $this->apiKey,
                        'Accept' => self::CONTENT_TYPE_JSON,
                        'Content-Type' => self::CONTENT_TYPE_JSON,
                    ],
                ]);
            }
        } else {
            $options = is_array($optionsOrClient) ? $optionsOrClient : [];
            $this->serverId = (string) ($options['server_id'] ?? 'localhost');
            $this->baseUrl  = rtrim($baseUrlOrConfig, '/') . '/api/v1/servers/' . $this->serverId . '/';
            $this->apiKey   = is_string($apiKeyOrLogger) ? $apiKeyOrLogger : '';

            $this->client = $optionsOrClient instanceof GuzzleClient ? $optionsOrClient : new GuzzleClient(array_merge([
                'base_uri'        => $this->baseUrl,
                'timeout'         => $options['timeout'] ?? 10.0,
                'connect_timeout' => $options['connect_timeout'] ?? 5.0,
                // SECURITY: Enable SSL verification in production
                'verify'          => $options['verify'] ?? true,
                'headers'         => [
                    'X-API-Key'    => $this->apiKey,
                    'Accept'       => self::CONTENT_TYPE_JSON,
                    'Content-Type' => self::CONTENT_TYPE_JSON,
                ],
            ], $options));
        }
    }

    public function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client->get($uri, ['query' => $query]);
            return $this->decode((string) $response->getBody());
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('GET', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function getRaw(string $uri, array $query = []): string
    {
        try {
            $response = $this->client->get($uri, ['query' => $query]);
            return (string) $response->getBody();
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('GET', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function post(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->post($uri, ['json' => $data]);
            return $this->decode((string) $response->getBody());
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('POST', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function patch(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->patch($uri, ['json' => $data]);
            $body = (string) $response->getBody();
            return $body !== '' ? $this->decode($body) : [];
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('PATCH', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function put(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->put($uri, ['json' => $data]);
            $body = (string) $response->getBody();
            return $body !== '' ? $this->decode($body) : [];
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('PUT', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function delete(string $uri): void
    {
        try {
            $this->client->delete($uri);
        } catch (RequestException $e) {
            throw new PowerApiException(
                $this->formatFailedMessage('DELETE', $uri, $e),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    private function formatFailedMessage(string $method, string $uri, RequestException $e): string
    {
        return sprintf('%s %s failed: %s', $method, $uri, $e->getMessage());
    }

    public function getServerId(): string
    {
        return $this->serverId;
    }

    private function decode(string $body): array
    {
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException $e) {
            throw new PowerApiException('Invalid JSON response from PowerDNS: ' . $e->getMessage());
        }
    }
}
