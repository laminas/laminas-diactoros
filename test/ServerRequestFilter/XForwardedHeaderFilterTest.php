<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\Exception\InvalidForwardedHeaderNameException;
use Laminas\Diactoros\Exception\InvalidProxyAddressException;
use Laminas\Diactoros\ServerRequestFilter\XForwardedHeaderFilter;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class XForwardedHeaderFilterTest extends TestCase
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies('192.168.1.0/24');

        $filteredRequest = $filter->filterRequest($request);
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies(
            '192.168.1.0/24',
            [$filter::HEADER_HOST, $filter::HEADER_PROTO]
        );

        $filteredRequest = $filter->filterRequest($request);
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies('192.168.1.0/24');

        $filteredRequest = $filter->filterRequest($request);
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
    public function testTrustingProxyListWithoutExplicitTrustedHeadersTrustsAllForwardedHeadersForTrustedProxies(
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies(['192.168.1.0/24', '10.1.0.0/16']);

        $filteredRequest = $filter->filterRequest($request);
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies(
            ['192.168.1.0/24', '10.1.0.0/16'],
            [$filter::HEADER_HOST, $filter::HEADER_PROTO]
        );

        $filteredRequest = $filter->filterRequest($request);
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustProxies(['192.168.1.0/24', '10.1.0.0/16']);

        $this->assertSame($request, $filter->filterRequest($request));
    }

    public function testPassingInvalidStringAddressForProxyRaisesException(): void
    {
        $filter = new XForwardedHeaderFilter();
        $this->expectException(InvalidProxyAddressException::class);
        $filter->trustProxies('192.168.1');
    }

    public function testPassingInvalidAddressInProxyListRaisesException(): void
    {
        $filter = new XForwardedHeaderFilter();
        $this->expectException(InvalidProxyAddressException::class);
        $filter->trustProxies(['192.168.1']);
    }

    public function testPassingInvalidForwardedHeaderNamesWhenTrustingProxyRaisesException(): void
    {
        $filter = new XForwardedHeaderFilter();
        $this->expectException(InvalidForwardedHeaderNameException::class);
        $filter->trustProxies('192.168.1.0/24', ['Host']);
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
                'Host'              => 'localhost',
                'X-Forwarded-Host'  => 'example.com,proxy.api.example.com',
            ]
        );

        $filter = new XForwardedHeaderFilter();
        $filter->trustAny();

        $this->assertSame($request, $filter->filterRequest($request));
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
                'Host'              => 'localhost',
                'X-Forwarded-Port'  => '8080,9000',
            ]
        );

        $filter = new XForwardedHeaderFilter();
        $filter->trustAny();

        $this->assertSame($request, $filter->filterRequest($request));
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

        $filter = new XForwardedHeaderFilter();
        $filter->trustAny();

        $this->assertSame($request, $filter->filterRequest($request));
    }
}
