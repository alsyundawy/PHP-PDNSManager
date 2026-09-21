<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Logger;
use App\Services\DNS\BindZoneService;
use PHPUnit\Framework\TestCase;

class BindZoneServiceTest extends TestCase
{
    private BindZoneService $service;

    protected function setUp(): void
    {
        $config = new Config(__DIR__ . '/../../config');
        $logger = new Logger($config);
        $this->service = new BindZoneService($logger);
    }

    public function testParseStandardBindZoneFile(): void
    {
        $bindData = <<<EOF
\$ORIGIN example.com.
\$TTL 3600
@       IN  SOA     ns1.example.com. hostmaster.example.com. (
                        2026092101 ; Serial
                        7200       ; Refresh
                        3600       ; Retry
                        1209600    ; Expire
                        3600 )     ; Minimum
@       IN  NS      ns1.example.com.
@       IN  NS      ns2.example.com.
@       IN  A       192.0.2.1
www     IN  CNAME   @
mail    IN  A       192.0.2.5
@       IN  MX  10  mail.example.com.
@       IN  TXT     "v=spf1 a mx ~all"
EOF;

        $parsed = $this->service->parse($bindData);

        $this->assertEquals('example.com.', $parsed['origin']);
        $this->assertEquals(3600, $parsed['ttl']);
        $this->assertNotEmpty($parsed['records']);

        $types = array_column($parsed['records'], 'type');
        $this->assertContains('SOA', $types);
        $this->assertContains('NS', $types);
        $this->assertContains('A', $types);
        $this->assertContains('CNAME', $types);
        $this->assertContains('MX', $types);
        $this->assertContains('TXT', $types);
    }

    public function testSerializeRecordsToBind(): void
    {
        $records = [
            [
                'name' => 'example.com.',
                'type' => 'SOA',
                'ttl' => 3600,
                'content' => 'ns1.example.com. hostmaster.example.com. 2026092101 7200 3600 1209600 3600',
            ],
            [
                'name' => 'example.com.',
                'type' => 'A',
                'ttl' => 3600,
                'content' => '192.0.2.1',
            ],
            [
                'name' => 'www.example.com.',
                'type' => 'CNAME',
                'ttl' => 3600,
                'content' => 'example.com.',
            ],
        ];

        $serialized = $this->service->serialize('example.com.', $records);

        $this->assertStringContainsString('$ORIGIN example.com.', $serialized);
        $this->assertStringContainsString('SOA', $serialized);
        $this->assertStringContainsString('192.0.2.1', $serialized);
        $this->assertStringContainsString('www.example.com.', $serialized);
    }
}
