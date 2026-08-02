<?php
declare(strict_types=1);
namespace Tests\Unit\Services\DNS;
use PHPUnit\Framework\TestCase;
use App\Services\DNS\ZoneService;
use App\Services\PowerDNS\Resource\ZoneResource;
use App\Services\PowerDNS\Resource\RecordResource;
use App\Services\PowerDNS\Resource\CryptokeyResource;
use App\Core\Logger;
use App\Core\Exceptions\ValidationException;

class ZoneServiceTest extends TestCase
{
    private $zoneResource;
    private $service;
    protected function setUp(): void {
        $this->zoneResource = $this->createMock(ZoneResource::class);
        $recordResource = $this->createMock(RecordResource::class);
        $cryptokeyResource = $this->createMock(CryptokeyResource::class);
        $logger = $this->createMock(Logger::class);
        $this->service = new ZoneService($this->zoneResource, $recordResource, $cryptokeyResource, $logger);
    }
    public function testGetAllZones(): void {
        $this->zoneResource->method('getAll')->willReturn([['name' => 'example.com']]);
        $result = $this->service->getAllZones();
        $this->assertCount(1, $result);
    }
    public function testCreateZoneValid(): void {
        $this->zoneResource->method('create')->willReturn(['id' => 'example.com']);
        $result = $this->service->createZone(['name' => 'example.com']);
        $this->assertEquals('example.com', $result['id']);
    }
    public function testCreateZoneInvalidName(): void {
        $this->expectException(ValidationException::class);
        $this->service->createZone(['name' => 'invalid domain']);
    }
}
