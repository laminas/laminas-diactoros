<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\DoNotFilter;
use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
use Laminas\Diactoros\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

use function Laminas\Diactoros\marshalHeadersFromSapi;
use function Laminas\Diactoros\marshalProtocolVersionFromSapi;
use function Laminas\Diactoros\normalizeServer;
use function Laminas\Diactoros\normalizeUploadedFiles;
use function str_replace;

final class ServerRequestFactoryTest extends TestCase
{
    public function testReturnsServerValueUnchangedIfHttpAuthorizationHeaderIsPresent(): void
    {
        $server = [
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_X_Foo'         => 'bar',
        ];
        $this->assertSame($server, normalizeServer($server));
    }

    public function testMarshalsExpectedHeadersFromServerArray(): void
    {
        $server = [
            'HTTP_COOKIE'        => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE'  => 'application/json',
            'HTTP_ACCEPT'        => 'application/json',
            'HTTP_X_FOO_BAR'     => 'FOOBAR',
            'CONTENT_MD5'        => 'CONTENT-MD5',
            'CONTENT_LENGTH'     => 'UNSPECIFIED',
        ];

        $expected = [
            'cookie'         => 'COOKIE',
            'authorization'  => 'token',
            'content-type'   => 'application/json',
            'accept'         => 'application/json',
            'x-foo-bar'      => 'FOOBAR',
            'content-md5'    => 'CONTENT-MD5',
            'content-length' => 'UNSPECIFIED',
        ];

        $this->assertSame($expected, marshalHeadersFromSapi($server));
    }

    public function testMarshalInvalidHeadersStrippedFromServerArray(): void
    {
        $server = [
            'COOKIE'             => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'MD5'                => 'CONTENT-MD5',
            'CONTENT_LENGTH'     => 'UNSPECIFIED',
        ];

        //Headers that don't begin with HTTP_ or CONTENT_ will not be returned
        $expected = [
            'authorization'  => 'token',
            'content-length' => 'UNSPECIFIED',
        ];
        $this->assertSame($expected, marshalHeadersFromSapi($server));
    }

    public function testMarshalsVariablesPrefixedByApacheFromServerArray(): void
    {
        // Non-prefixed versions will be preferred
        $server = [
            'HTTP_X_FOO_BAR'              => 'nonprefixed',
            'REDIRECT_HTTP_AUTHORIZATION' => 'token',
            'REDIRECT_HTTP_X_FOO_BAR'     => 'prefixed',
        ];

        $expected = [
            'authorization' => 'token',
            'x-foo-bar'     => 'nonprefixed',
        ];

        $this->assertEquals($expected, marshalHeadersFromSapi($server));
    }

    public function testCanCreateServerRequestViaFromGlobalsMethod(): void
    {
        $server = [
            'SERVER_PROTOCOL' => '1.1',
            'HTTP_HOST'       => 'example.com',
            'HTTP_ACCEPT'     => 'application/json',
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/foo/bar',
            'QUERY_STRING'    => 'bar=baz',
        ];

        $cookies = $query = $body = [
            'bar' => 'baz',
        ];

        $cookies['cookies'] = true;
        $query['query']     = true;
        $body['body']       = true;
        $files              = [
            'files' => [
                'tmp_name' => 'php://temp',
                'size'     => 0,
                'error'    => 0,
                'name'     => 'foo.bar',
                'type'     => 'text/plain',
            ],
        ];
        $expectedFiles      = [
            'files' => new UploadedFile('php://temp', 0, 0, 'foo.bar', 'text/plain'),
        ];

        $request = ServerRequestFactory::fromGlobals($server, $query, $body, $cookies, $files);
        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertSame($cookies, $request->getCookieParams());
        $this->assertSame($query, $request->getQueryParams());
        $this->assertSame($body, $request->getParsedBody());
        $this->assertEquals($expectedFiles, $request->getUploadedFiles());
        $this->assertEmpty($request->getAttributes());
        $this->assertSame('1.1', $request->getProtocolVersion());
    }

    public function testFromGlobalsUsesCookieHeaderInsteadOfCookieSuperGlobal(): void
    {
        $_COOKIE                = [
            'foo_bar' => 'bat',
        ];
        $_SERVER['HTTP_COOKIE'] = 'foo_bar=baz';

        $request = ServerRequestFactory::fromGlobals();
        $this->assertSame(['foo_bar' => 'baz'], $request->getCookieParams());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState
     */
    public function testCreateFromGlobalsShouldPreserveKeysWhenCreatedWithAZeroValue(): void
    {
        $_SERVER['HTTP_ACCEPT']    = '0';
        $_SERVER['CONTENT_LENGTH'] = '0';

        $request = ServerRequestFactory::fromGlobals();
        $this->assertSame('0', $request->getHeaderLine('accept'));
        $this->assertSame('0', $request->getHeaderLine('content-length'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState
     */
    public function testCreateFromGlobalsShouldNotPreserveKeysWhenCreatedWithAnEmptyValue(): void
    {
        $_SERVER['HTTP_ACCEPT']    = '';
        $_SERVER['CONTENT_LENGTH'] = '';

        $request = ServerRequestFactory::fromGlobals();

        $this->assertFalse($request->hasHeader('accept'));
        $this->assertFalse($request->hasHeader('content-length'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFromGlobalsUsesCookieSuperGlobalWhenCookieHeaderIsNotSet(): void
    {
        $_COOKIE = [
            'foo_bar' => 'bat',
        ];

        $request = ServerRequestFactory::fromGlobals();
        $this->assertSame(['foo_bar' => 'bat'], $request->getCookieParams());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, array<non-empty-string, non-empty-string>}> */
    public function cookieHeaderValues(): array
    {
        return [
            'ows-without-fold'    => [
                "\tfoo=bar ",
                ['foo' => 'bar'],
            ],
            'url-encoded-value'   => [
                'foo=bar%3B+',
                ['foo' => 'bar; '],
            ],
            'double-quoted-value' => [
                'foo="bar"',
                ['foo' => 'bar'],
            ],
            'multiple-pairs'      => [
                'foo=bar; baz="bat"; bau=bai',
                ['foo' => 'bar', 'baz' => 'bat', 'bau' => 'bai'],
            ],
            'same-name-pairs'     => [
                'foo=bar; foo="bat"',
                ['foo' => 'bat'],
            ],
            'period-in-name'      => [
                'foo.bar=baz',
                ['foo.bar' => 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider cookieHeaderValues
     * @param non-empty-string $cookieHeader
     * @param array<non-empty-string, non-empty-string> $expectedCookies
     */
    public function testCookieHeaderVariations(string $cookieHeader, array $expectedCookies): void
    {
        $_SERVER['HTTP_COOKIE'] = $cookieHeader;

        $request = ServerRequestFactory::fromGlobals();
        $this->assertSame($expectedCookies, $request->getCookieParams());
    }

    public function testNormalizeServerUsesMixedCaseAuthorizationHeaderFromApacheWhenPresent(): void
    {
        $server = normalizeServer([], static fn(): array => ['Authorization' => 'foobar']);

        $this->assertArrayHasKey('HTTP_AUTHORIZATION', $server);
        $this->assertSame('foobar', $server['HTTP_AUTHORIZATION']);
    }

    public function testNormalizeServerUsesLowerCaseAuthorizationHeaderFromApacheWhenPresent(): void
    {
        $server = normalizeServer([], static fn(): array => ['authorization' => 'foobar']);

        $this->assertArrayHasKey('HTTP_AUTHORIZATION', $server);
        $this->assertSame('foobar', $server['HTTP_AUTHORIZATION']);
    }

    public function testNormalizeServerReturnsArrayUnalteredIfApacheHeadersDoNotContainAuthorization(): void
    {
        $expected = ['FOO_BAR' => 'BAZ'];

        $server = normalizeServer($expected, static fn(): array => []);

        $this->assertSame($expected, $server);
    }

    /**
     * @group 57
     * @group 56
     */
    public function testNormalizeFilesReturnsOnlyActualFilesWhenOriginalFilesContainsNestedAssociativeArrays(): void
    {
        $files = [
            'fooFiles' => [
                'tmp_name' => ['file' => 'php://temp'],
                'size'     => ['file' => 0],
                'error'    => ['file' => 0],
                'name'     => ['file' => 'foo.bar'],
                'type'     => ['file' => 'text/plain'],
            ],
        ];

        $normalizedFiles = normalizeUploadedFiles($files);

        $this->assertCount(1, $normalizedFiles['fooFiles']);
    }

    public function testMarshalProtocolVersionRisesExceptionIfVersionIsNotRecognized(): void
    {
        $this->expectException(UnexpectedValueException::class);
        marshalProtocolVersionFromSapi(['SERVER_PROTOCOL' => 'dadsa/1.0']);
    }

    public function testMarshalProtocolReturnsDefaultValueIfHeaderIsNotPresent(): void
    {
        $version = marshalProtocolVersionFromSapi([]);
        $this->assertSame('1.1', $version);
    }

    /**
     * @dataProvider marshalProtocolVersionProvider
     * @param non-empty-string $protocol
     * @param non-empty-string $expected
     */
    public function testMarshalProtocolVersionReturnsHttpVersions(string $protocol, string $expected): void
    {
        $version = marshalProtocolVersionFromSapi(['SERVER_PROTOCOL' => $protocol]);
        $this->assertSame($expected, $version);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string}> */
    public function marshalProtocolVersionProvider(): array
    {
        return [
            'HTTP/1.0' => ['HTTP/1.0', '1.0'],
            'HTTP/1.1' => ['HTTP/1.1', '1.1'],
            'HTTP/2'   => ['HTTP/2', '2'],
        ];
    }

    public function testServerRequestFactoryHasAWritableEmptyBody(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/');
        $body    = $request->getBody();

        $this->assertTrue($body->isWritable());
        $this->assertTrue($body->isSeekable());
        $this->assertSame(0, $body->getSize());
    }

    /**
     * @psalm-return iterable<string, array{
     *     0: array<string, string>,
     *     1: string,
     *     2: string,
     *     3: string
     * }>
     */
    public function serverContentMap(): iterable
    {
        yield 'content-type' => [
            [
                'HTTP_CONTENT_TYPE' => 'text/plain',
                'CONTENT_TYPE'      => 'application/x-octect-stream',
            ],
            'CONTENT_TYPE',
            'application/x-octect-stream',
            'application/x-octect-stream',
        ];

        yield 'content-length' => [
            [
                'HTTP_CONTENT_LENGTH' => '24',
                'CONTENT_LENGTH'      => '42',
            ],
            'CONTENT_LENGTH',
            '42',
            '42',
        ];

        yield 'content-md5' => [
            [
                'HTTP_CONTENT_MD5' => '3112373cbdba2b74d26d231f1aa5318b',
                'CONTENT_MD5'      => 'a918b672e563fb911e8c59ea1c56819a',
            ],
            'CONTENT_MD5',
            'a918b672e563fb911e8c59ea1c56819a',
            'a918b672e563fb911e8c59ea1c56819a',
        ];

        yield 'env-value-last-default-behavior' => [
            [
                'HTTP_CONTENT_API_PASSWORD' => 'password from header',
                'CONTENT_API_PASSWORD'      => 'password from env',
            ],
            'CONTENT_API_PASSWORD',
            'password from env',
            'password from env',
        ];

        yield 'env-value-first-default-behavior' => [
            [
                'CONTENT_API_PASSWORD'      => 'password from env',
                'HTTP_CONTENT_API_PASSWORD' => 'password from header',
            ],
            'CONTENT_API_PASSWORD',
            'password from header',
            'password from env',
        ];

        yield 'env-value-last-strict-content-headers' => [
            [
                'HTTP_CONTENT_API_PASSWORD'                      => 'password from header',
                'CONTENT_API_PASSWORD'                           => 'password from env',
                'LAMINAS_DIACTOROS_STRICT_CONTENT_HEADER_LOOKUP' => 'true',
            ],
            'CONTENT_API_PASSWORD',
            'password from header',
            'password from env',
        ];

        yield 'env-value-first-strict-content-headers' => [
            [
                'CONTENT_API_PASSWORD'                           => 'password from env',
                'LAMINAS_DIACTOROS_STRICT_CONTENT_HEADER_LOOKUP' => 'true',
                'HTTP_CONTENT_API_PASSWORD'                      => 'password from header',
            ],
            'CONTENT_API_PASSWORD',
            'password from header',
            'password from env',
        ];
    }

    /**
     * @dataProvider serverContentMap
     * @psalm-param array<string, string> $server
     */
    public function testDoesNotMarshalAllContentPrefixedServerVarsAsHeaders(
        array $server,
        string $key,
        string $expectedHeaderValue,
        string $expectedServerValue
    ): void {
        $request    = ServerRequestFactory::fromGlobals($server);
        $headerName = str_replace('_', '-', $key);

        $this->assertSame($expectedHeaderValue, $request->getHeaderLine($headerName));
        $this->assertSame($expectedServerValue, $request->getServerParams()[$key]);
    }

    public function testReturnsFilteredRequestBasedOnRequestFilterProvided(): void
    {
        $expectedRequest = new ServerRequest();
        $filter          = new class ($expectedRequest) implements FilterServerRequestInterface {
            public function __construct(private ServerRequestInterface $request)
            {
            }

            public function __invoke(ServerRequestInterface $request): ServerRequestInterface
            {
                return $this->request;
            }
        };

        $request = ServerRequestFactory::fromGlobals(
            ['REMOTE_ADDR' => '127.0.0.1'],
            ['foo' => 'bar'],
            null,
            null,
            null,
            $filter
        );

        $this->assertSame($expectedRequest, $request);
    }

    public function testHonorsHostHeaderOverServerNameWhenMarshalingUrl(): void
    {
        $server = [
            'SERVER_NAME'     => 'localhost',
            'SERVER_PORT'     => '80',
            'SERVER_ADDR'     => '172.22.0.4',
            'REMOTE_PORT'     => '36852',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'DOCUMENT_ROOT'   => '/var/www/public',
            'DOCUMENT_URI'    => '/index.php',
            'REQUEST_URI'     => '/api/messagebox-schema',
            'PATH_TRANSLATED' => '/var/www/public',
            'PATH_INFO'       => '',
            'SCRIPT_NAME'     => '/index.php',
            'REQUEST_METHOD'  => 'GET',
            'SCRIPT_FILENAME' => '/var/www/public/index.php',
            // headers
            'HTTP_HOST' => 'example.com',
        ];

        $request = ServerRequestFactory::fromGlobals(
            $server,
            null,
            null,
            null,
            null,
            new DoNotFilter()
        );

        $uri = $request->getUri();
        $this->assertSame('example.com', $uri->getHost());
    }

    /**
     * @psalm-return iterable<string, array{
     *     0: string
     * }>
     */
    public function invalidHostHeaders(): iterable
    {
        return [
            'comma' => ['example.com,example.net'],
            'space' => ['example com'],
            'tab'   => ["example\tcom"],
        ];
    }

    /**
     * @dataProvider invalidHostHeaders
     */
    public function testRejectsDuplicatedHostHeader(string $host): void
    {
        $server = [
            'HTTP_HOST' => $host,
        ];

        $request = ServerRequestFactory::fromGlobals(
            $server,
            null,
            null,
            null,
            null,
            new DoNotFilter()
        );

        $uri = $request->getUri();
        $this->assertSame('', $uri->getHost());
    }
}
