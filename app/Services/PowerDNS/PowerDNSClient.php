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
    private GuzzleClient $client;
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey, array $options = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/api/v1/servers/localhost/';
        $this->apiKey  = $apiKey;

        $this->client = new GuzzleClient(array_merge([
            'base_uri'        => $this->baseUrl,
            'timeout'         => $options['timeout'] ?? 10.0,
            'connect_timeout' => $options['connect_timeout'] ?? 5.0,
            // SECURITY: Enable SSL verification in production
            'verify'          => $options['verify'] ?? true,
            'headers'         => [
                'X-API-Key'    => $this->apiKey,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ], $options));
    }

    public function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client->get($path, ['query' => $query]);
            return $this->decode((string) $response->getBody());
        } catch (RequestException $e) {
            throw new PowerApiException(
                'GET ' . $path . ' failed: ' . $e->getMessage(),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function post(string $path, array $data = []): array
    {
        try {
            $response = $this->client->post($path, ['json' => $data]);
            return $this->decode((string) $response->getBody());
        } catch (RequestException $e) {
            throw new PowerApiException(
                'POST ' . $path . ' failed: ' . $e->getMessage(),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function patch(string $path, array $data = []): array
    {
        try {
            $response = $this->client->patch($path, ['json' => $data]);
            $body = (string) $response->getBody();
            return $body !== '' ? $this->decode($body) : [];
        } catch (RequestException $e) {
            throw new PowerApiException(
                'PATCH ' . $path . ' failed: ' . $e->getMessage(),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function put(string $path, array $data = []): array
    {
        try {
            $response = $this->client->put($path, ['json' => $data]);
            $body = (string) $response->getBody();
            return $body !== '' ? $this->decode($body) : [];
        } catch (RequestException $e) {
            throw new PowerApiException(
                'PUT ' . $path . ' failed: ' . $e->getMessage(),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->client->delete($path);
        } catch (RequestException $e) {
            throw new PowerApiException(
                'DELETE ' . $path . ' failed: ' . $e->getMessage(),
                $e->getResponse()?->getStatusCode() ?? 502
            );
        }
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
