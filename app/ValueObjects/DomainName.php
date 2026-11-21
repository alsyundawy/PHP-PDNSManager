<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * DomainName Value Object adhering to RFC 1034, RFC 1035, and RFC 2181.
 */
final class DomainName
{
    private string $value;

    public function __construct(string $domain)
    {
        $trimmed = trim($domain);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Domain name cannot be empty.');
        }

        // Canonical lowercase with trailing dot
        $canonical = strtolower(rtrim($trimmed, '.')) . '.';

        if (strlen($canonical) > 255) {
            throw new InvalidArgumentException('Domain name exceeds 253 characters limit.');
        }

        // Validate each label (must not exceed 63 chars)
        $labels = explode('.', rtrim($canonical, '.'));
        foreach ($labels as $label) {
            if ($label === '') {
                throw new InvalidArgumentException("Domain name contains invalid empty label: {$domain}");
            }
            if (strlen($label) > 63) {
                throw new InvalidArgumentException("Domain label '{$label}' exceeds 63 characters.");
            }
            if (!preg_match('/^[a-z0-9_\-\*\/]+$/i', $label)) {
                throw new InvalidArgumentException("Domain label '{$label}' contains invalid characters.");
            }
        }

        $this->value = $canonical;
    }

    public static function fromString(string $domain): self
    {
        return new self($domain);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toUnrooted(): string
    {
        return rtrim($this->value, '.');
    }

    public function isReverse(): bool
    {
        return str_ends_with($this->value, '.in-addr.arpa.') || str_ends_with($this->value, '.ip6.arpa.');
    }

    public function isIpv4Reverse(): bool
    {
        return str_ends_with($this->value, '.in-addr.arpa.');
    }

    public function isIpv6Reverse(): bool
    {
        return str_ends_with($this->value, '.ip6.arpa.');
    }

    public function getParent(): ?self
    {
        $unrooted = $this->toUnrooted();
        $pos = strpos($unrooted, '.');
        if ($pos === false) {
            return null;
        }

        return new self(substr($unrooted, $pos + 1));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
