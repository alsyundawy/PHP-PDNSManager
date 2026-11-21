<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Console\Kernel;
use App\Core\Application;

class CliKernelTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        $this->kernel = new Kernel($app);
    }

    public function testHelpCommandReturnsZero(): void
    {
        ob_start();
        $code = $this->kernel->handle(['bin/pdns', 'help']);
        $output = ob_get_clean();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('PHP-PDNSManager Enterprise Edition', (string) $output);
        $this->assertStringContainsString('Available Commands:', (string) $output);
    }

    public function testHelpFlagReturnsZero(): void
    {
        ob_start();
        $code = $this->kernel->handle(['bin/pdns', '--help']);
        $output = ob_get_clean();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('Usage:', (string) $output);
    }

    public function testUnknownCommandReturnsOne(): void
    {
        ob_start();
        $code = $this->kernel->handle(['bin/pdns', 'nonexistent:command']);
        $output = ob_get_clean();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('Usage:', (string) $output);
    }

    public function testZoneNotifyMissingArgumentReturnsOne(): void
    {
        ob_start();
        $code = $this->kernel->handle(['bin/pdns', 'zone:notify']);
        $output = ob_get_clean();

        $this->assertSame(1, $code);
    }

    public function testZoneRectifyMissingArgumentReturnsOne(): void
    {
        ob_start();
        $code = $this->kernel->handle(['bin/pdns', 'zone:rectify']);
        $output = ob_get_clean();

        $this->assertSame(1, $code);
    }
}
