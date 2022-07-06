<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\Exception\InvalidForwardedHeaderNameException;
use Laminas\Diactoros\Exception\InvalidProxyAddressException;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
use PHPUnit\Framework\TestCase;

class FilterUsingXForwardedHeadersTest extends TestCase
{
    public function testTrustingStringProxyWithoutSpecifyingTrustedHeadersTrustsAllForwardedHeadersForThatProxy(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.1.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(['192.168.1.0/24']);

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertNotSame($request->getUri(), $filteredUri);
        $this->assertSame('example.com', $filteredUri->getHost());
        $this->assertSame(4433, $filteredUri->getPort());
        $this->assertSame('https', $filteredUri->getScheme());
    }

    public function testTrustingStringProxyWithSpecificTrustedHeadersTrustsOnlyThoseHeadersForTrustedProxy(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.1.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(
            ['192.168.1.0/24'],
            [FilterUsingXForwardedHeaders::HEADER_HOST, FilterUsingXForwardedHeaders::HEADER_PROTO]
        );

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertNotSame($request->getUri(), $filteredUri);
        $this->assertSame('example.com', $filteredUri->getHost());
        $this->assertSame(80, $filteredUri->getPort());
        $this->assertSame('https', $filteredUri->getScheme());
    }

    public function testFilterDoesNothingWhenAddressNotFromTrustedProxy(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '10.0.0.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(['192.168.1.0/24']);

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertSame($request->getUri(), $filteredUri);
    }

    /** @psalm-return iterable<string, array{0: string}> */
    public function trustedProxyList(): iterable
    {
        yield 'private-class-a-subnet' => ['10.1.1.1'];
        yield 'private-class-c-subnet' => ['192.168.1.1'];
    }

    /** @dataProvider trustedProxyList */
    public function testTrustingProxyListWithoutExplicitTrustedHeadersTrustsAllForwardedRequestsForTrustedProxies(
        string $remoteAddr
    ): void {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => $remoteAddr],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(['192.168.1.0/24', '10.1.0.0/16']);

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertNotSame($request->getUri(), $filteredUri);
        $this->assertSame('example.com', $filteredUri->getHost());
        $this->assertSame(4433, $filteredUri->getPort());
        $this->assertSame('https', $filteredUri->getScheme());
    }

    /** @dataProvider trustedProxyList */
    public function testTrustingProxyListWithSpecificTrustedHeadersTrustsOnlyThoseHeaders(string $remoteAddr): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => $remoteAddr],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(
            ['192.168.1.0/24', '10.1.0.0/16'],
            [FilterUsingXForwardedHeaders::HEADER_HOST, FilterUsingXForwardedHeaders::HEADER_PROTO]
        );

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertNotSame($request->getUri(), $filteredUri);
        $this->assertSame('example.com', $filteredUri->getHost());
        $this->assertSame(80, $filteredUri->getPort());
        $this->assertSame('https', $filteredUri->getScheme());
    }

    /** @psalm-return iterable<string, array{0: string}> */
    public function untrustedProxyList(): iterable
    {
        yield 'private-class-a-subnet' => ['10.0.0.1'];
        yield 'private-class-c-subnet' => ['192.168.168.1'];
    }

    /** @dataProvider untrustedProxyList */
    public function testFilterDoesNothingWhenAddressNotInTrustedProxyList(string $remoteAddr): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => $remoteAddr],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustProxies(['192.168.1.0/24', '10.1.0.0/16']);

        $this->assertSame($request, $filter($request));
    }

    public function testPassingInvalidAddressInProxyListRaisesException(): void
    {
        $this->expectException(InvalidProxyAddressException::class);
        FilterUsingXForwardedHeaders::trustProxies(['192.168.1']);
    }

    public function testPassingInvalidForwardedHeaderNamesWhenTrustingProxyRaisesException(): void
    {
        $this->expectException(InvalidForwardedHeaderNameException::class);
        /**
         * @psalm-suppress InvalidArgument
         */
        FilterUsingXForwardedHeaders::trustProxies(['192.168.1.0/24'], ['Host']);
    }

    public function testListOfForwardedHostsIsConsideredUntrusted(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.1.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'             => 'localhost',
                'X-Forwarded-Host' => 'example.com,proxy.api.example.com',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustAny();

        $this->assertSame($request, $filter($request));
    }

    public function testListOfForwardedPortsIsConsideredUntrusted(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.1.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'             => 'localhost',
                'X-Forwarded-Port' => '8080,9000',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustAny();

        $this->assertSame($request, $filter($request));
    }

    public function testListOfForwardedProtosIsConsideredUntrusted(): void
    {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.1.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Proto' => 'http,https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustAny();

        $this->assertSame($request, $filter($request));
    }

    /** @psalm-return iterable<string, array{0: string}> */
    public function trustedReservedNetworkList(): iterable
    {
        yield 'ipv4-localhost' => ['127.0.0.1'];
        yield 'ipv4-class-a' => ['10.10.10.10'];
        yield 'ipv4-class-b' => ['172.16.16.16'];
        yield 'ipv4-class-c' => ['192.168.2.1'];
        yield 'ipv6-localhost' => ['::1'];
        yield 'ipv6-private' => ['fdb4:d239:27bc:1d9f:0001:0001:0001:0001'];
        yield 'ipv6-local-link' => ['fe80:0000:0000:0000:abcd:abcd:abcd:abcd'];
    }

    /** @dataProvider trustedReservedNetworkList */
    public function testTrustReservedSubnetsProducesFilterThatAcceptsAddressesFromThoseSubnets(
        string $remoteAddr
    ): void {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => $remoteAddr],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustReservedSubnets();

        $filteredRequest = $filter($request);
        $filteredUri     = $filteredRequest->getUri();
        $this->assertNotSame($request->getUri(), $filteredUri);
        $this->assertSame('example.com', $filteredUri->getHost());
        $this->assertSame(4433, $filteredUri->getPort());
        $this->assertSame('https', $filteredUri->getScheme());
    }

    /** @psalm-return iterable<string, array{0: string}> */
    public function unreservedNetworkAddressList(): iterable
    {
        yield 'ipv4-no-localhost' => ['128.0.0.1'];
        yield 'ipv4-no-class-a' => ['19.10.10.10'];
        yield 'ipv4-not-class-b' => ['173.16.16.16'];
        yield 'ipv4-not-class-c' => ['193.168.2.1'];
        yield 'ipv6-not-localhost' => ['::2'];
        yield 'ipv6-not-private' => ['fab4:d239:27bc:1d9f:0001:0001:0001:0001'];
        yield 'ipv6-not-local-link' => ['ef80:0000:0000:0000:abcd:abcd:abcd:abcd'];
    }

    /** @dataProvider unreservedNetworkAddressList */
    public function testTrustReservedSubnetsProducesFilterThatRejectsAddressesNotFromThoseSubnets(
        string $remoteAddr
    ): void {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => $remoteAddr],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com',
                'X-Forwarded-Port'  => '4433',
                'X-Forwarded-Proto' => 'https',
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustReservedSubnets();

        $filteredRequest = $filter($request);
        $this->assertSame($request, $filteredRequest);
    }

    /** @psalm-return iterable<string, array{0: string, 1: string}> */
    public function xForwardedProtoValues(): iterable
    {
        yield 'https-lowercase'  => ['https', 'https'];
        yield 'https-uppercase'  => ['HTTPS', 'https'];
        yield 'https-mixed-case' => ['hTTpS', 'https'];
        yield 'http-lowercase'   => ['http', 'http'];
        yield 'http-uppercase'   => ['HTTP', 'http'];
        yield 'http-mixed-case'  => ['hTTp', 'http'];
        yield 'unknown-value'    => ['foo', 'http'];
        yield 'empty'            => ['', 'http'];
    }

    /** @dataProvider xForwardedProtoValues */
    public function testOnlyHonorsXForwardedProtoIfValueResolvesToHTTPS(
        string $xForwarededProto,
        string $expectedScheme
    ): void {
        $request = new ServerRequest(
            ['REMOTE_ADDR' => '192.168.0.1'],
            [],
            'http://localhost:80/foo/bar',
            'GET',
            'php://temp',
            [
                'Host'              => 'localhost',
                'X-Forwarded-Proto' => $xForwarededProto,
            ]
        );

        $filter = FilterUsingXForwardedHeaders::trustReservedSubnets();

        $filteredRequest = $filter($request);
        $uri             = $filteredRequest->getUri();
        $this->assertSame($expectedScheme, $uri->getScheme());
    }
}
