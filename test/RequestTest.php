<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    /** @var Request */
    protected $request;

    protected function setUp(): void
    {
        $this->request = new Request();
    }

    public function testMethodIsGetByDefault(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    public function testMethodMutatorReturnsCloneWithChangedMethod(): void
    {
        $request = $this->request->withMethod('POST');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('POST', $request->getMethod());
    }

    /** @return array<int, array{0: mixed}> */
    public function invalidMethod(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider invalidMethod
     * @param mixed $method
     */
    public function testWithInvalidMethod($method): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress MixedArgument */
        $this->request->withMethod($method);
    }

    public function testReturnsUnpopulatedUriByDefault(): void
    {
        $uri = $this->request->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEmpty($uri->getScheme());
        $this->assertEmpty($uri->getUserInfo());
        $this->assertEmpty($uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEmpty($uri->getPath());
        $this->assertEmpty($uri->getQuery());
        $this->assertEmpty($uri->getFragment());
    }

    public function testConstructorRaisesExceptionForInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        new Request(['TOTALLY INVALID']);
    }

    public function testWithUriReturnsNewInstanceWithNewUri(): void
    {
        $request = $this->request->withUri(new Uri('https://example.com:10082/foo/bar?baz=bat'));
        $this->assertNotSame($this->request, $request);
        $request2 = $request->withUri(new Uri('/baz/bat?foo=bar'));
        $this->assertNotSame($this->request, $request2);
        $this->assertNotSame($request, $request2);
        $this->assertSame('/baz/bat?foo=bar', (string) $request2->getUri());
    }

    public function testConstructorCanAcceptAllMessageParts(): void
    {
        $uri     = new Uri('http://example.com/');
        $body    = new Stream('php://memory');
        $headers = [
            'x-foo' => ['bar'],
        ];
        $request = new Request(
            $uri,
            'POST',
            $body,
            $headers
        );

        $this->assertSame($uri, $request->getUri());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($body, $request->getBody());
        $testHeaders = $request->getHeaders();
        foreach ($headers as $key => $value) {
            $this->assertArrayHasKey($key, $testHeaders);
            $this->assertSame($value, $testHeaders[$key]);
        }
    }

    public function testDefaultStreamIsWritable(): void
    {
        $request = new Request();
        $request->getBody()->write("test");

        $this->assertSame("test", (string) $request->getBody());
    }

    /** @return array<string, array{0: mixed}> */
    public function invalidRequestUri(): array
    {
        return [
            'true'     => [true],
            'false'    => [false],
            'int'      => [1],
            'float'    => [1.1],
            'array'    => [['http://example.com']],
            'stdClass' => [(object) ['href' => 'http://example.com']],
        ];
    }

    /**
     * @dataProvider invalidRequestUri
     * @param mixed $uri
     */
    public function testConstructorRaisesExceptionForInvalidUri($uri): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI');

        /** @psalm-suppress MixedArgument */
        new Request($uri);
    }

    /** @return array<string, array{0: string}> */
    public function invalidRequestMethod(): array
    {
        return [
            'bad-string' => ['BOGUS METHOD'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethod
     */
    public function testConstructorRaisesExceptionForInvalidMethod(string $method): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method');

        new Request(null, $method);
    }

    /** @return array<string, array{0: string}> */
    public function customRequestMethods(): array
    {
        return [
            /* WebDAV methods */
            'TRACE'     => ['TRACE'],
            'PROPFIND'  => ['PROPFIND'],
            'PROPPATCH' => ['PROPPATCH'],
            'MKCOL'     => ['MKCOL'],
            'COPY'      => ['COPY'],
            'MOVE'      => ['MOVE'],
            'LOCK'      => ['LOCK'],
            'UNLOCK'    => ['UNLOCK'],
            'UNLOCK'    => ['UNLOCK'],
            /* Arbitrary methods */
            '#!ALPHA-1234&%' => ['#!ALPHA-1234&%'],
        ];
    }

    /**
     * @dataProvider customRequestMethods
     */
    public function testAllowsCustomRequestMethodsThatFollowSpec(string $method): void
    {
        $request = new Request(null, $method);
        $this->assertSame($method, $request->getMethod());
    }

    /** @return array<string, array{0: mixed}> */
    public function invalidRequestBody(): array
    {
        return [
            'true'     => [true],
            'false'    => [false],
            'int'      => [1],
            'float'    => [1.1],
            'array'    => [['BODY']],
            'stdClass' => [(object) ['body' => 'BODY']],
        ];
    }

    /**
     * @dataProvider invalidRequestBody
     * @param mixed $body
     */
    public function testConstructorRaisesExceptionForInvalidBody($body): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        /** @psalm-suppress MixedArgument */
        new Request(null, null, $body);
    }

    /** @return array<string, array{0: mixed[], 1?: string}> */
    public function invalidHeaderTypes(): array
    {
        return [
            'indexed-array' => [[['INVALID']], 'header name'],
            'null'          => [['x-invalid-null' => null]],
            'true'          => [['x-invalid-true' => true]],
            'false'         => [['x-invalid-false' => false]],
            'object'        => [['x-invalid-object' => (object) ['INVALID']]],
        ];
    }

    /**
     * @dataProvider invalidHeaderTypes
     * @param mixed[] $headers
     */
    public function testConstructorRaisesExceptionForInvalidHeaders(
        array $headers,
        string $contains = 'header value type'
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($contains);

        new Request(null, null, 'php://memory', $headers);
    }

    public function testRequestTargetIsSlashWhenNoUriPresent(): void
    {
        $request = new Request();
        $this->assertSame('/', $request->getRequestTarget());
    }

    public function testRequestTargetIsSlashWhenUriHasNoPathOrQuery(): void
    {
        $request = (new Request())
            ->withUri(new Uri('http://example.com'));
        $this->assertSame('/', $request->getRequestTarget());
    }

    /** @return array<string, array{0: RequestInterface, 1: string}> */
    public function requestsWithUri(): array
    {
        return [
            'absolute-uri'            => [
                (new Request())
                ->withUri(new Uri('https://api.example.com/user'))
                ->withMethod('POST'),
                '/user',
            ],
            'absolute-uri-with-query' => [
                (new Request())
                ->withUri(new Uri('https://api.example.com/user?foo=bar'))
                ->withMethod('POST'),
                '/user?foo=bar',
            ],
            'relative-uri'            => [
                (new Request())
                ->withUri(new Uri('/user'))
                ->withMethod('GET'),
                '/user',
            ],
            'relative-uri-with-query' => [
                (new Request())
                ->withUri(new Uri('/user?foo=bar'))
                ->withMethod('GET'),
                '/user?foo=bar',
            ],
        ];
    }

    /**
     * @dataProvider requestsWithUri
     */
    public function testReturnsRequestTargetWhenUriIsPresent(RequestInterface $request, string $expected): void
    {
        $this->assertSame($expected, $request->getRequestTarget());
    }

    /** @return array<string, array{0:string}> */
    public function validRequestTargets(): array
    {
        return [
            'asterisk-form'         => ['*'],
            'authority-form'        => ['api.example.com'],
            'absolute-form'         => ['https://api.example.com/users'],
            'absolute-form-query'   => ['https://api.example.com/users?foo=bar'],
            'origin-form-path-only' => ['/users'],
            'origin-form'           => ['/users?id=foo'],
        ];
    }

    /**
     * @dataProvider validRequestTargets
     */
    public function testCanProvideARequestTarget(string $requestTarget): void
    {
        $request = (new Request())->withRequestTarget($requestTarget);
        $this->assertSame($requestTarget, $request->getRequestTarget());
    }

    public function testRequestTargetCannotContainWhitespace(): void
    {
        $request = new Request();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target');

        $request->withRequestTarget('foo bar baz');
    }

    public function testRequestTargetDoesNotCacheBetweenInstances(): void
    {
        $request    = (new Request())->withUri(new Uri('https://example.com/foo/bar'));
        $original   = $request->getRequestTarget();
        $newRequest = $request->withUri(new Uri('http://mwop.net/bar/baz'));
        $this->assertNotSame($original, $newRequest->getRequestTarget());
    }

    public function testSettingNewUriResetsRequestTarget(): void
    {
        $request    = (new Request())->withUri(new Uri('https://example.com/foo/bar'));
        $newRequest = $request->withUri(new Uri('http://mwop.net/bar/baz'));

        $this->assertNotSame($request->getRequestTarget(), $newRequest->getRequestTarget());
    }

    public function testGetHeadersContainsHostHeaderIfUriWithHostIsPresent(): void
    {
        $request = new Request('http://example.com');
        $headers = $request->getHeaders();
        $this->assertArrayHasKey('Host', $headers);
        $this->assertStringContainsString('example.com', $headers['Host'][0]);
    }

    public function testGetHeadersContainsHostHeaderIfUriWithHostIsDeleted(): void
    {
        $request = (new Request('http://example.com'))->withoutHeader('host');
        $headers = $request->getHeaders();
        $this->assertArrayHasKey('Host', $headers);
        $this->assertContains('example.com', $headers['Host']);
    }

    public function testGetHeadersContainsNoHostHeaderIfNoUriPresent(): void
    {
        $request = new Request();
        $headers = $request->getHeaders();
        $this->assertArrayNotHasKey('Host', $headers);
    }

    public function testGetHeadersContainsNoHostHeaderIfUriDoesNotContainHost(): void
    {
        $request = new Request(new Uri());
        $headers = $request->getHeaders();
        $this->assertArrayNotHasKey('Host', $headers);
    }

    public function testGetHostHeaderReturnsUriHostWhenPresent(): void
    {
        $request = new Request('http://example.com');
        $header  = $request->getHeader('host');
        $this->assertSame(['example.com'], $header);
    }

    public function testGetHostHeaderReturnsUriHostWhenHostHeaderDeleted(): void
    {
        $request = (new Request('http://example.com'))->withoutHeader('host');
        $header  = $request->getHeader('host');
        $this->assertSame(['example.com'], $header);
    }

    public function testGetHostHeaderReturnsEmptyArrayIfNoUriPresent(): void
    {
        $request = new Request();
        $this->assertSame([], $request->getHeader('host'));
    }

    public function testGetHostHeaderReturnsEmptyArrayIfUriDoesNotContainHost(): void
    {
        $request = new Request(new Uri());
        $this->assertSame([], $request->getHeader('host'));
    }

    public function testGetHostHeaderLineReturnsUriHostWhenPresent(): void
    {
        $request = new Request('http://example.com');
        $header  = $request->getHeaderLine('host');
        $this->assertStringContainsString('example.com', $header);
    }

    public function testGetHostHeaderLineReturnsEmptyStringIfNoUriPresent(): void
    {
        $request = new Request();
        $this->assertEmpty($request->getHeaderLine('host'));
    }

    public function testGetHostHeaderLineReturnsEmptyStringIfUriDoesNotContainHost(): void
    {
        $request = new Request(new Uri());
        $this->assertEmpty($request->getHeaderLine('host'));
    }

    public function testHostHeaderSetFromUriOnCreationIfNoHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com');
        $this->assertTrue($request->hasHeader('Host'));
        $this->assertSame('www.example.com', $request->getHeaderLine('host'));
    }

    public function testHostHeaderNotSetFromUriOnCreationIfHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com', null, 'php://memory', ['Host' => 'www.test.com']);
        $this->assertSame('www.test.com', $request->getHeaderLine('host'));
    }

    public function testPassingPreserveHostFlagWhenUpdatingUriDoesNotUpdateHostHeader(): void
    {
        $request = (new Request())
            ->withAddedHeader('Host', 'example.com');

        $uri = (new Uri())->withHost('www.example.com');
        $new = $request->withUri($uri, true);

        $this->assertSame('example.com', $new->getHeaderLine('Host'));
    }

    public function testNotPassingPreserveHostFlagWhenUpdatingUriWithoutHostDoesNotUpdateHostHeader(): void
    {
        $request = (new Request())
            ->withAddedHeader('Host', 'example.com');

        $uri = new Uri();
        $new = $request->withUri($uri);

        $this->assertSame('example.com', $new->getHeaderLine('Host'));
    }

    public function testHostHeaderUpdatesToUriHostAndPortWhenPreserveHostDisabledAndNonStandardPort(): void
    {
        $request = (new Request())
            ->withAddedHeader('Host', 'example.com');

        $uri = (new Uri())
            ->withHost('www.example.com')
            ->withPort(10081);
        $new = $request->withUri($uri);

        $this->assertSame('www.example.com:10081', $new->getHeaderLine('Host'));
    }

    /** @return array<string, array{0: string, 1: string|list<string>}> */
    public function headersWithInjectionVectors(): array
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    /**
     * @dataProvider headersWithInjectionVectors
     * @param string|list<string> $value
     */
    public function testConstructorRaisesExceptionForHeadersWithCRLFVectors(string $name, $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Request(null, null, 'php://memory', [$name => $value]);
    }

    /** @return array<string, array{0: string}> */
    public function hostHeaderKeys(): array
    {
        return [
            'lowercase'         => ['host'],
            'mixed-4'           => ['hosT'],
            'mixed-3-4'         => ['hoST'],
            'reverse-titlecase' => ['hOST'],
            'uppercase'         => ['HOST'],
            'mixed-1-2-3'       => ['HOSt'],
            'mixed-1-2'         => ['HOst'],
            'titlecase'         => ['Host'],
            'mixed-1-4'         => ['HosT'],
            'mixed-1-2-4'       => ['HOsT'],
            'mixed-1-3-4'       => ['HoST'],
            'mixed-1-3'         => ['HoSt'],
            'mixed-2-3'         => ['hOSt'],
            'mixed-2-4'         => ['hOsT'],
            'mixed-2'           => ['hOst'],
            'mixed-3'           => ['hoSt'],
        ];
    }

    /**
     * @dataProvider hostHeaderKeys
     */
    public function testWithUriAndNoPreserveHostWillOverwriteHostHeaderRegardlessOfOriginalCase(string $hostKey): void
    {
        $request = (new Request())
            ->withHeader($hostKey, 'example.com');

        $uri  = new Uri('http://example.org/foo/bar');
        $new  = $request->withUri($uri);
        $host = $new->getHeaderLine('host');
        $this->assertSame('example.org', $host);
        $headers = $new->getHeaders();
        $this->assertArrayHasKey('Host', $headers);
        if ($hostKey !== 'Host') {
            $this->assertArrayNotHasKey($hostKey, $headers);
        }
    }
}
