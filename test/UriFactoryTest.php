<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\Uri;
use Laminas\Diactoros\UriFactory;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function sprintf;
use function str_contains;
use function strtolower;

class UriFactoryTest extends TestCase
{
    public function testCreateFromSapiUsesIISUnencodedUrlValueIfPresentAndUrlWasRewritten(): void
    {
        $server = [
            'IIS_WasUrlRewritten' => '1',
            'UNENCODED_URL'       => '/foo/bar',
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame($server['UNENCODED_URL'], $uri->getPath());
    }

    public function testCreateFromSapiStripsSchemeHostAndPortInformationWhenPresent(): void
    {
        $server = [
            'REQUEST_URI' => 'http://example.com:8000/foo/bar',
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame('/foo/bar', $uri->getPath());
    }

    public function testCreateFromSapiUsesOrigPathInfoIfPresent(): void
    {
        $server = [
            'ORIG_PATH_INFO' => '/foo/bar',
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame('/foo/bar', $uri->getPath());
    }

    public function testCreateFromSapiFallsBackToRoot(): void
    {
        $server = [];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame('/', $uri->getPath());
    }

    public function testMarshalHostAndPortUsesHostHeaderWhenPresent(): void
    {
        $headers = ['Host' => ['example.com']];

        $uri = UriFactory::createFromSapi([], $headers);

        $this->assertSame('example.com', $uri->getHost());
        $this->assertNull($uri->getPort());
    }

    public function testMarshalHostAndPortWillDetectPortInHostHeaderWhenPresent(): void
    {
        $headers = ['Host' => ['example.com:8000']];

        $uri = UriFactory::createFromSapi([], $headers);

        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8000, $uri->getPort());
    }

    public function testMarshalHostAndPortReturnsEmptyValuesIfNoHostHeaderAndNoServerName(): void
    {
        $uri = UriFactory::createFromSapi([], []);

        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
    }

    public function testMarshalHostAndPortReturnsServerNameForHostWhenPresent(): void
    {
        $server  = [
            'SERVER_NAME' => 'example.com',
        ];
        $headers = [];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertSame('example.com', $uri->getHost());
        $this->assertNull($uri->getPort());
    }

    public function testMarshalHostAndPortReturnsServerPortForPortWhenPresentWithServerName(): void
    {
        $server = [
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => 8000,
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8000, $uri->getPort());
    }

    public function testMarshalHostAndPortReturnsServerNameForHostIfServerAddrPresentButHostIsNotIpv6Address(): void
    {
        $server = [
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_NAME' => 'example.com',
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame('example.com', $uri->getHost());
    }

    public function testMarshalHostAndPortReturnsServerAddrForHostIfPresentAndHostIsIpv6Address(): void
    {
        $server = [
            'SERVER_ADDR' => 'FE80::0202:B3FF:FE1E:8329',
            'SERVER_NAME' => '[FE80::0202:B3FF:FE1E:8329]',
            'SERVER_PORT' => 8000,
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame(strtolower('[FE80::0202:B3FF:FE1E:8329]'), $uri->getHost());
        $this->assertSame(8000, $uri->getPort());
    }

    public function testMarshalHostAndPortWillDetectPortInIpv6StyleHost(): void
    {
        $server = [
            'SERVER_ADDR' => 'FE80::0202:B3FF:FE1E:8329',
            'SERVER_NAME' => '[FE80::0202:B3FF:FE1E:8329:80]',
        ];

        $uri = UriFactory::createFromSapi($server, []);

        $this->assertSame(strtolower('[FE80::0202:B3FF:FE1E:8329]'), $uri->getHost());
        $this->assertNull($uri->getPort());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string}> */
    public function httpsParamProvider(): array
    {
        return [
            'lowercase' => ['https'],
            'uppercase' => ['HTTPS'],
        ];
    }

    /**
     * @dataProvider httpsParamProvider
     * @param non-empty-string $param
     */
    public function testMarshalUriDetectsHttpsSchemeFromServerValue(string $param): void
    {
        $server  = [
            $param => 'on',
        ];
        $headers = ['Host' => ['example.com']];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('https', $uri->getScheme());
    }

    /** @return iterable<string, array{non-empty-string, 'off'|'OFF'}> */
    public function httpsDisableParamProvider(): iterable
    {
        foreach ($this->httpsParamProvider() as $key => $data) {
            $param = array_shift($data);
            foreach (['lowercase-off', 'uppercase-off'] as $type) {
                $key   = sprintf('%s-%s', $key, $type);
                $value = str_contains($type, 'lowercase') ? 'off' : 'OFF';
                yield $key => [$param, $value];
            }
        }
    }

    /**
     * @dataProvider httpsDisableParamProvider
     * @param non-empty-string $param
     * @param 'off'|'OFF' $value
     */
    public function testMarshalUriUsesHttpSchemeIfHttpsServerValueEqualsOff(string $param, string $value): void
    {
        $server  = [
            $param => $value,
        ];
        $headers = ['Host' => ['example.com']];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('http', $uri->getScheme());
    }

    public function testMarshalUriStripsQueryStringFromRequestUri(): void
    {
        $server  = [
            'REQUEST_URI' => '/foo/bar?foo=bar',
        ];
        $headers = [
            'Host' => ['example.com'],
        ];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('/foo/bar', $uri->getPath());
    }

    public function testMarshalUriInjectsQueryStringFromServer(): void
    {
        $server  = [
            'REQUEST_URI'  => '/foo/bar?foo=bar',
            'QUERY_STRING' => 'bar=baz',
        ];
        $headers = [
            'Host' => ['example.com'],
        ];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('bar=baz', $uri->getQuery());
    }

    public function testMarshalUriInjectsFragmentFromServer(): void
    {
        $server  = [
            'REQUEST_URI' => '/foo/bar#foo',
        ];
        $headers = [
            'Host' => ['example.com'],
        ];

        $uri = UriFactory::createFromSapi($server, $headers);

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('foo', $uri->getFragment());
    }
}
