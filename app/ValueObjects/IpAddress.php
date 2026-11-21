<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * IpAddress Value Object with IPv4 and IPv6 Reverse PTR Zone Calculation.
 */
final class IpAddress
{
    private string $ip;
    private int $version;

    public function __construct(string $ip)
    {
        $trimmed = trim($ip);
        if (filter_var($trimmed, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ip = $trimmed;
            $this->version = 4;
        } elseif (filter_var($trimmed, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->ip = strtolower($trimmed);
            $this->version = 6;
        } else {
            throw new InvalidArgumentException("Invalid IPv4 or IPv6 address: {$ip}");
        }
    }

    public static function fromString(string $ip): self
    {
        return new self($ip);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isV4(): bool
    {
        return $this->version === 4;
    }

    public function isV6(): bool
    {
        return $this->version === 6;
    }

    public function toString(): string
    {
        return $this->ip;
    }

    /**
     * Generate the RFC-compliant Reverse PTR record name.
     * e.g. 192.0.2.1 -> 1.2.0.192.in-addr.arpa.
     * e.g. 2001:db8::1 -> 1.0.0.0.0.0.0.0...8.b.d.0.1.0.0.2.ip6.arpa.
     */
    public function toReversePtrName(): string
    {
        if ($this->isV4()) {
            $octets = explode('.', $this->ip);
            return implode('.', array_reverse($octets)) . '.in-addr.arpa.';
        }

        // IPv6 full 32-nibble expansion
        $hex = bin2hex((string) inet_pton($this->ip));
        $nibbles = str_split($hex);
        return implode('.', array_reverse($nibbles)) . '.ip6.arpa.';
    }

    /**
     * Generate the standard parent Reverse Zone Name for a given subnet prefix length.
     * Default for IPv4 is /24 (e.g. 192.0.2.1 -> 2.0.192.in-addr.arpa.)
     * Default for IPv6 is /64 (e.g. 2001:db8::1 -> 0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.)
     */
    public function toReverseZoneName(int $prefixLength = 0): string
    {
        if ($this->isV4()) {
            $prefix = $prefixLength > 0 ? $prefixLength : 24;
            $octets = explode('.', $this->ip);

            if ($prefix === 24) {
                return "{$octets[2]}.{$octets[1]}.{$octets[0]}.in-addr.arpa.";
            }
            if ($prefix === 16) {
                return "{$octets[1]}.{$octets[0]}.in-addr.arpa.";
            }
            if ($prefix === 8) {
                return "{$octets[0]}.in-addr.arpa.";
            }

            return "{$octets[2]}.{$octets[1]}.{$octets[0]}.in-addr.arpa.";
        }

        // IPv6
        $prefix = $prefixLength > 0 ? $prefixLength : 64;
        $hex = bin2hex((string) inet_pton($this->ip));
        $nibbleCount = (int) ($prefix / 4);
        $zoneHex = substr($hex, 0, $nibbleCount);
        $nibbles = str_split($zoneHex);

        return implode('.', array_reverse($nibbles)) . '.ip6.arpa.';
    }

    public function equals(self $other): bool
    {
        return $this->ip === $other->ip;
    }

    public function __toString(): string
    {
        return $this->ip;
    }
}
