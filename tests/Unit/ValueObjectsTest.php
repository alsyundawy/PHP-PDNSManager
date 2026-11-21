<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\ValueObjects\DomainName;
use App\ValueObjects\IpAddress;
use InvalidArgumentException;

class ValueObjectsTest extends TestCase
{
    public function testDomainNameCanonicalizationAndValidation(): void
    {
        $domain = new DomainName('Example.COM');
        $this->assertSame('example.com.', $domain->toString());
        $this->assertSame('example.com', $domain->toUnrooted());
        $this->assertFalse($domain->isReverse());

        $parent = $domain->getParent();
        $this->assertNotNull($parent);
        $this->assertSame('com.', $parent->toString());
    }

    public function testDomainNameReverseDetection(): void
    {
        $v4Rev = new DomainName('2.0.192.in-addr.arpa.');
        $this->assertTrue($v4Rev->isReverse());
        $this->assertTrue($v4Rev->isIpv4Reverse());
        $this->assertFalse($v4Rev->isIpv6Reverse());

        $v6Rev = new DomainName('0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.');
        $this->assertTrue($v6Rev->isReverse());
        $this->assertTrue($v6Rev->isIpv6Reverse());
        $this->assertFalse($v6Rev->isIpv4Reverse());
    }

    public function testDomainNameEmptyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DomainName('');
    }

    public function testIpAddressV4ToReversePtrAndZone(): void
    {
        $ip = new IpAddress('192.0.2.15');
        $this->assertTrue($ip->isV4());
        $this->assertFalse($ip->isV6());
        $this->assertSame('15.2.0.192.in-addr.arpa.', $ip->toReversePtrName());
        $this->assertSame('2.0.192.in-addr.arpa.', $ip->toReverseZoneName(24));
        $this->assertSame('0.192.in-addr.arpa.', $ip->toReverseZoneName(16));
        $this->assertSame('192.in-addr.arpa.', $ip->toReverseZoneName(8));
    }

    public function testIpAddressV6ToReversePtrAndZone(): void
    {
        $ip = new IpAddress('2001:db8::1');
        $this->assertTrue($ip->isV6());
        $this->assertFalse($ip->isV4());

        $ptr = $ip->toReversePtrName();
        $this->assertStringEndsWith('.ip6.arpa.', $ptr);
        $this->assertStringStartsWith('1.0.0.0.0.0.0.0.', $ptr);

        $zone = $ip->toReverseZoneName(64);
        $this->assertSame('0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.', $zone);
    }

    public function testIpAddressInvalidThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpAddress('invalid.ip.address');
    }
}
