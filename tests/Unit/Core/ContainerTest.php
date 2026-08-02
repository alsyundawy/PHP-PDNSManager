<?php
declare(strict_types=1);
namespace Tests\Unit\Core;
use PHPUnit\Framework\TestCase;
use App\Core\Container;

class ContainerTest extends TestCase
{
    private Container $container;
    protected function setUp(): void { $this->container = new Container(); }
    public function testBindAndResolve(): void {
        $this->container->bind('test', fn() => new \stdClass());
        $this->assertInstanceOf(\stdClass::class, $this->container->get('test'));
    }
    public function testSingleton(): void {
        $this->container->singleton('test', fn() => new \stdClass());
        $this->assertSame($this->container->get('test'), $this->container->get('test'));
    }
    public function testAutowire(): void {
        $this->assertInstanceOf(\stdClass::class, $this->container->get(\stdClass::class));
    }
    public function testHas(): void {
        $this->container->bind('test', fn() => null);
        $this->assertTrue($this->container->has('test'));
        $this->assertFalse($this->container->has('nonexistent'));
    }
}
