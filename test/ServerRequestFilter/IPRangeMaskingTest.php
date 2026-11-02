<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ServerRequestFilter\IPRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IPRangeMaskingTest extends TestCase
{
    /** @return iterable<string, array{string, string, bool}> */
    public static function cidrBoundaries(): iterable
    {
        yield 'v4 inside /12' => ['172.31.255.255', '172.16.0.0/12', true];
        yield 'v4 outside /12' => ['172.32.0.0', '172.16.0.0/12', false];
        yield 'v4 /0' => ['8.8.8.8', '0.0.0.0/0', true];
        yield 'v4 /32 exact' => ['10.0.0.1', '10.0.0.1/32', true];
        yield 'v4 /32 other' => ['10.0.0.2', '10.0.0.1/32', false];
        yield 'v6 fc00::/7 includes fd' => ['fd12::1', 'fc00::/7', true];
        yield 'v6 fc00::/7 excludes fe' => ['fe00::1', 'fc00::/7', false];
        yield 'v6 fe80::/10 boundary' => ['febf::1', 'fe80::/10', true];
        yield 'v6 fe80::/10 outside' => ['fec0::1', 'fe80::/10', false];
        yield 'v6 ::1/128' => ['::1', '::1/128', true];
        yield 'v6 ::2 not ::1/128' => ['::2', '::1/128', false];
        yield 'v4 ip vs v6 cidr' => ['10.0.0.1', '::/0', false];
    }

    #[DataProvider('cidrBoundaries')]
    public function testIpRangeMatchingBoundaries(string $ip, string $cidr, bool $expected): void
    {
        $this->assertSame($expected, IPRange::matches($ip, $cidr));
    }

    /**
     * An IPv4 CIDR must never match an IPv6 address, including with a /0 mask.
     */
    public function testIpv4ZeroMaskDoesNotMatchIpv6Address(): void
    {
        $this->assertFalse(IPRange::matches('::1', '10.0.0.0/8'));
        $this->assertFalse(IPRange::matches('::1', '0.0.0.0/0'));
    }

    /**
     * A CIDR whose base address has host bits set (e.g. "fd00::1/64") should be
     * masked for IPv6 the same way it already is for IPv4.
     */
    public function testIpv6CidrWithHostBitsBehavesLikeIpv4(): void
    {
        $this->assertTrue(IPRange::matches('10.0.0.2', '10.0.0.1/24'), 'IPv4 masks the subnet');
        $this->assertTrue(IPRange::matches('fd00::2', 'fd00::1/64'), 'IPv6 should mask the subnet too');
    }
}
