<?php
declare(strict_types=1);
namespace Tests\Unit\Services\PowerDNS;
use PHPUnit\Framework\TestCase;
use App\Services\PowerDNS\PowerDNSClient;
use App\Core\Config;
use App\Core\Logger;
use App\Core\Exceptions\PowerApiException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class PowerDNSClientTest extends TestCase
{
    private $mockHandler;
    private $client;
    protected function setUp(): void {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $config = $this->createMock(Config::class);
        $config->method('get')->willReturnMap([
            ['powerdns.api_url', 'http://127.0.0.1:8081'],
            ['powerdns.api_key', 'test-key'],
            ['powerdns.timeout', 30.0],
            ['powerdns.verify_ssl', true],
            ['powerdns.server_id', 'localhost'],
        ]);
        $logger = $this->createMock(Logger::class);
        $this->client = new PowerDNSClient($config, $logger);
    }
    public function testGetRequest(): void {
        $this->mockHandler->append(new Response(200, [], json_encode(['zones' => []])));
        $result = $this->client->get('zones');
        $this->assertIsArray($result);
    }
    public function testErrorResponse(): void {
        $this->mockHandler->append(new Response(404, [], json_encode(['error' => 'Not found'])));
        $this->expectException(PowerApiException::class);
        $this->client->get('zones/nonexistent');
    }
}
