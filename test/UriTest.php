<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

use function sprintf;

class UriTest extends TestCase
{
    public function testConstructorSetsAllProperties(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('local.example.com', $uri->getHost());
        $this->assertSame(3001, $uri->getPort());
        $this->assertSame('user:pass@local.example.com:3001', $uri->getAuthority());
        $this->assertSame('/foo', $uri->getPath());
        $this->assertSame('bar=baz', $uri->getQuery());
        $this->assertSame('quz', $uri->getFragment());
    }

    public function testCanSerializeToString(): void
    {
        $url = 'https://user:pass@local.example.com:3001/foo?bar=baz#quz';
        $uri = new Uri($url);
        $this->assertSame($url, (string) $uri);
    }

    public function testWithSchemeReturnsNewInstanceWithNewScheme(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('http');
        $this->assertNotSame($uri, $new);
        $this->assertSame('http', $new->getScheme());
        $this->assertSame('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    public function testWithSchemeReturnsSameInstanceWithSameScheme(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('https');
        $this->assertSame($uri, $new);
        $this->assertSame('https', $new->getScheme());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    public function testWithUserInfoReturnsNewInstanceWithProvidedUser(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew');
        $this->assertNotSame($uri, $new);
        $this->assertSame('matthew', $new->getUserInfo());
        $this->assertSame('https://matthew@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    public function testWithUserInfoReturnsNewInstanceWithProvidedUserAndPassword(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew', 'laminas');
        $this->assertNotSame($uri, $new);
        $this->assertSame('matthew:laminas', $new->getUserInfo());
        $this->assertSame('https://matthew:laminas@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    public function testWithUserInfoThrowExceptionIfPasswordIsNotString(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        $uri->withUserInfo('matthew', 1);
    }

    public function testWithUserInfoReturnsSameInstanceIfUserAndPasswordAreSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('user', 'pass');
        $this->assertSame($uri, $new);
        $this->assertSame('user:pass', $new->getUserInfo());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string, non-empty-string}> */
    public function userInfoProvider(): array
    {
        // @codingStandardsIgnoreStart
        return [
            // name       => [ user,              credential, expected ]
            'valid-chars' => ['foo',              'bar',      'foo:bar'],
            'colon'       => ['foo:bar',          'baz:bat',  'foo%3Abar:baz%3Abat'],
            'at'          => ['user@example.com', 'cred@foo', 'user%40example.com:cred%40foo'],
            'percent'     => ['%25',              '%25',      '%25:%25'],
            'invalid-enc' => ['%ZZ',              '%GG',      '%25ZZ:%25GG'],
            'invalid-utf' => ["\x21\x92",         '!?',       '!%92:!%3F'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider userInfoProvider
     * @param non-empty-string $user
     * @param non-empty-string $credential
     * @param non-empty-string $expected
     */
    public function testWithUserInfoEncodesUsernameAndPassword(string $user, string $credential, string $expected): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo($user, $credential);

        $this->assertSame($expected, $new->getUserInfo());
    }

    public function testWithHostReturnsNewInstanceWithProvidedHost(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('getlaminas.org');
        $this->assertNotSame($uri, $new);
        $this->assertSame('getlaminas.org', $new->getHost());
        $this->assertSame('https://user:pass@getlaminas.org:3001/foo?bar=baz#quz', (string) $new);
    }

    public function testWithHostReturnsSameInstanceWithProvidedHostIsSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('local.example.com');
        $this->assertSame($uri, $new);
        $this->assertSame('local.example.com', $new->getHost());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /** @return non-empty-array<non-empty-string, array{null|positive-int|numeric-string}> */
    public function validPorts(): array
    {
        return [
            'null'       => [null],
            'int'        => [3000],
            'string-int' => ['3000'],
        ];
    }

    /**
     * @dataProvider validPorts
     * @param null|positive-int|numeric-string $port
     */
    public function testWithPortReturnsNewInstanceWithProvidedPort($port): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        /** @psalm-suppress PossiblyInvalidArgument */
        $new = $uri->withPort($port);
        $this->assertNotSame($uri, $new);
        $this->assertEquals($port, $new->getPort());
        $this->assertSame(
            sprintf('https://user:pass@local.example.com%s/foo?bar=baz#quz', $port === null ? '' : ':' . $port),
            (string) $new
        );
    }

    public function testWithPortReturnsSameInstanceWithProvidedPortIsSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        /** @psalm-suppress PossiblyInvalidArgument,InvalidArgument */
        $new = $uri->withPort('3001');
        $this->assertSame($uri, $new);
        $this->assertSame(3001, $new->getPort());
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidPorts(): array
    {
        return [
            'true'      => [true],
            'false'     => [false],
            'string'    => ['string'],
            'float'     => [55.5],
            'array'     => [[3000]],
            'object'    => [(object) ['port' => 3000]],
            'zero'      => [0],
            'too-small' => [-1],
            'too-big'   => [65536],
        ];
    }

    /**
     * @dataProvider invalidPorts
     */
    public function testWithPortRaisesExceptionForInvalidPorts(mixed $port): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port');

        /** @psalm-suppress MixedArgument */
        $uri->withPort($port);
    }

    public function testWithPathReturnsNewInstanceWithProvidedPath(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPath('/bar/baz');
        $this->assertNotSame($uri, $new);
        $this->assertSame('/bar/baz', $new->getPath());
        $this->assertSame('https://user:pass@local.example.com:3001/bar/baz?bar=baz#quz', (string) $new);
    }

    public function testWithPathReturnsSameInstanceWithProvidedPathSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPath('/foo');
        $this->assertSame($uri, $new);
        $this->assertSame('/foo', $new->getPath());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidPaths(): array
    {
        return [
            'null'     => [null],
            'true'     => [true],
            'false'    => [false],
            'array'    => [['/bar/baz']],
            'object'   => [(object) ['/bar/baz']],
            'query'    => ['/bar/baz?bat=quz'],
            'fragment' => ['/bar/baz#bat'],
        ];
    }

    /**
     * @dataProvider invalidPaths
     */
    public function testWithPathRaisesExceptionForInvalidPaths(mixed $path): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path');

        /** @psalm-suppress MixedArgument */
        $uri->withPath($path);
    }

    public function testWithQueryReturnsNewInstanceWithProvidedQuery(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withQuery('baz=bat');
        $this->assertNotSame($uri, $new);
        $this->assertSame('baz=bat', $new->getQuery());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?baz=bat#quz', (string) $new);
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidQueryStrings(): array
    {
        return [
            'null'     => [null],
            'true'     => [true],
            'false'    => [false],
            'array'    => [['baz=bat']],
            'object'   => [(object) ['baz=bat']],
            'fragment' => ['baz=bat#quz'],
        ];
    }

    /**
     * @dataProvider invalidQueryStrings
     */
    public function testWithQueryRaisesExceptionForInvalidQueryStrings(mixed $query): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query string');

        /** @psalm-suppress MixedArgument */
        $uri->withQuery($query);
    }

    public function testWithFragmentReturnsNewInstanceWithProvidedFragment(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withFragment('qat');
        $this->assertNotSame($uri, $new);
        $this->assertSame('qat', $new->getFragment());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#qat', (string) $new);
    }

    public function testWithFragmentReturnsSameInstanceWithProvidedFragmentSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withFragment('quz');
        $this->assertSame($uri, $new);
        $this->assertSame('quz', $new->getFragment());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string}> */
    public function authorityInfo(): array
    {
        return [
            'host-only'      => ['http://foo.com/bar',         'foo.com'],
            'host-port'      => ['http://foo.com:3000/bar',    'foo.com:3000'],
            'user-host'      => ['http://me@foo.com/bar',      'me@foo.com'],
            'user-host-port' => ['http://me@foo.com:3000/bar', 'me@foo.com:3000'],
        ];
    }

    /**
     * @dataProvider authorityInfo
     * @param non-empty-string $url
     * @param non-empty-string $expected
     */
    public function testRetrievingAuthorityReturnsExpectedValues(string $url, string $expected): void
    {
        $uri = new Uri($url);
        $this->assertSame($expected, $uri->getAuthority());
    }

    public function testCanEmitOriginFormUrl(): void
    {
        $url = '/foo/bar?baz=bat';
        $uri = new Uri($url);
        $this->assertSame($url, (string) $uri);
    }

    public function testSettingEmptyPathOnAbsoluteUriReturnsAnEmptyPath(): void
    {
        $uri = new Uri('http://example.com/foo');
        $new = $uri->withPath('');
        $this->assertSame('', $new->getPath());
    }

    public function testStringRepresentationOfAbsoluteUriWithNoPathSetsAnEmptyPath(): void
    {
        $uri = new Uri('http://example.com');
        $this->assertSame('http://example.com', (string) $uri);
    }

    public function testEmptyPathOnOriginFormRemainsAnEmptyPath(): void
    {
        $uri = new Uri('?foo=bar');
        $this->assertSame('', $uri->getPath());
    }

    public function testStringRepresentationOfOriginFormWithNoPathRetainsEmptyPath(): void
    {
        $uri = new Uri('?foo=bar');
        $this->assertSame('?foo=bar', (string) $uri);
    }

    public function testConstructorRaisesExceptionForSeriouslyMalformedURI(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Uri('http:///www.php-fig.org/');
    }

    public function testMutatingSchemeStripsOffDelimiter(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withScheme('https://');
        $this->assertSame('https', $new->getScheme());
    }

    public function testESchemeStripsOffDelimiter(): void
    {
        $uri = new Uri('https://example.com');
        $new = $uri->withScheme('://');
        $this->assertSame('', $new->getScheme());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string}> */
    public function invalidSchemes(): array
    {
        return [
            'mailto' => ['mailto'],
            'ftp'    => ['ftp'],
            'telnet' => ['telnet'],
            'ssh'    => ['ssh'],
            'git'    => ['git'],
        ];
    }

    /**
     * @dataProvider invalidSchemes
     * @param non-empty-string $scheme
     */
    public function testConstructWithUnsupportedSchemeRaisesAnException(string $scheme): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported scheme');

        new Uri($scheme . '://example.com');
    }

    /**
     * @dataProvider invalidSchemes
     * @param non-empty-string $scheme
     */
    public function testMutatingWithUnsupportedSchemeRaisesAnException(string $scheme): void
    {
        $uri = new Uri('http://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported scheme');

        $uri->withScheme($scheme);
    }

    public function testPathIsNotPrefixedWithSlashIfSetWithoutOne(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        $this->assertSame('foo/bar', $new->getPath());
    }

    public function testPathNotSlashPrefixedIsEmittedWithSlashDelimiterWhenUriIsCastToString(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('foo/bar');
        $this->assertSame('http://example.com/foo/bar', $new->__toString());
    }

    public function testStripsQueryPrefixIfPresent(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withQuery('?foo=bar');
        $this->assertSame('foo=bar', $new->getQuery());
    }

    public function testEncodeFragmentPrefixIfPresent(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withFragment('#/foo/bar');
        $this->assertSame('%23/foo/bar', $new->getFragment());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, positive-int}> */
    public function standardSchemePortCombinations(): array
    {
        return [
            'http'  => ['http', 80],
            'https' => ['https', 443],
        ];
    }

    /**
     * @dataProvider standardSchemePortCombinations
     * @param non-empty-string $scheme
     * @param positive-int $port
     */
    public function testAuthorityOmitsPortForStandardSchemePortCombinations(string $scheme, int $port): void
    {
        $uri = (new Uri())
            ->withHost('example.com')
            ->withScheme($scheme)
            ->withPort($port);
        $this->assertSame('example.com', $uri->getAuthority());
    }

    /** @return non-empty-array<string, array{'withScheme'|'withUserInfo'|'withHost'|'withPort'|'withPath'|'withQuery'|'withFragment', non-empty-string|positive-int}> */
    public function mutations(): array
    {
        return [
            'scheme'    => ['withScheme', 'https'],
            'user-info' => ['withUserInfo', 'foo'],
            'host'      => ['withHost', 'www.example.com'],
            'port'      => ['withPort', 8080],
            'path'      => ['withPath', '/changed'],
            'query'     => ['withQuery', 'changed=value'],
            'fragment'  => ['withFragment', 'changed'],
        ];
    }

    /**
     * @dataProvider mutations
     * @param 'withScheme'|'withUserInfo'|'withHost'|'withPort'|'withPath'|'withQuery'|'withFragment' $method
     * @param non-empty-string|positive-int $value
     */
    public function testMutationResetsUriStringPropertyInClone(string $method, $value): void
    {
        $uri    = new Uri('http://example.com/path?query=string#fragment');
        $string = (string) $uri;

        $r = new ReflectionObject($uri);
        $p = $r->getProperty('uriString');
        $p->setAccessible(true);
        $this->assertSame($string, $p->getValue($uri));

        $test = $uri->{$method}($value);
        $r2   = new ReflectionObject($uri);
        $p2   = $r2->getProperty('uriString');
        $p2->setAccessible(true);
        $this->assertNull($p2->getValue($test));

        $this->assertSame($string, $p->getValue($uri));
    }

    /**
     * @group 40
     */
    public function testPathIsProperlyEncoded(): void
    {
        $uri      = (new Uri())->withPath('/foo^bar');
        $expected = '/foo%5Ebar';
        $this->assertSame($expected, $uri->getPath());
    }

    public function testPathDoesNotBecomeDoubleEncoded(): void
    {
        $uri      = (new Uri())->withPath('/foo%5Ebar');
        $expected = '/foo%5Ebar';
        $this->assertSame($expected, $uri->getPath());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string}> */
    public function queryStringsForEncoding(): array
    {
        return [
            'key-only'        => ['k^ey', 'k%5Eey'],
            'key-value'       => ['k^ey=valu`', 'k%5Eey=valu%60'],
            'array-key-only'  => ['key[]', 'key%5B%5D'],
            'array-key-value' => ['key[]=valu`', 'key%5B%5D=valu%60'],
            'complex'         => ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider queryStringsForEncoding
     * @param non-empty-string $query
     * @param non-empty-string $expected
     */
    public function testQueryIsProperlyEncoded(string $query, string $expected): void
    {
        $uri = (new Uri())->withQuery($query);
        $this->assertSame($expected, $uri->getQuery());
    }

    /**
     * @dataProvider queryStringsForEncoding
     * @param non-empty-string $query
     * @param non-empty-string $expected
     */
    public function testQueryIsNotDoubleEncoded(string $query, string $expected): void
    {
        $uri = (new Uri())->withQuery($expected);
        $this->assertSame($expected, $uri->getQuery());
    }

    /**
     * @group 40
     */
    public function testFragmentIsProperlyEncoded(): void
    {
        $uri      = (new Uri())->withFragment('/p^th?key^=`bar#b@z');
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $this->assertSame($expected, $uri->getFragment());
    }

    /**
     * @group 40
     */
    public function testFragmentIsNotDoubleEncoded(): void
    {
        $expected = '/p%5Eth?key%5E=%60bar%23b@z';
        $uri      = (new Uri())->withFragment($expected);
        $this->assertSame($expected, $uri->getFragment());
    }

    /** @return non-empty-array<string, array{'withScheme'|'withUserInfo'|'withHost'|'withPath'|'withQuery'|'withFragment', mixed}> */
    public function invalidStringComponentValues(): array
    {
        $methods = [
            'withScheme',
            'withUserInfo',
            'withHost',
            'withPath',
            'withQuery',
            'withFragment',
        ];

        $values = [
            'null'       => null,
            'true'       => true,
            'false'      => false,
            'zero'       => 0,
            'int'        => 1,
            'zero-float' => 0.0,
            'float'      => 1.1,
            'array'      => ['value'],
            'object'     => (object) ['value' => 'value'],
        ];

        $combinations = [];
        foreach ($methods as $method) {
            foreach ($values as $type => $value) {
                $key                = sprintf('%s-%s', $method, $type);
                $combinations[$key] = [$method, $value];
            }
        }

        return $combinations;
    }

    /**
     * @dataProvider invalidStringComponentValues
     * @param 'withScheme'|'withUserInfo'|'withHost'|'withPath'|'withQuery'|'withFragment' $method
     */
    public function testPassingInvalidValueToWithMethodRaisesException(string $method, mixed $value): void
    {
        $uri = new Uri('https://example.com/');

        $this->expectException(InvalidArgumentException::class);

        $uri->$method($value);
    }

    public function testUtf8Uri(): void
    {
        $uri = new Uri('http://ουτοπία.δπθ.gr/');

        $this->assertSame('ουτοπία.δπθ.gr', $uri->getHost());
    }

    /**
     * @dataProvider utf8PathsDataProvider
     * @param non-empty-string $url
     * @param non-empty-string $result
     */
    public function testUtf8Path(string $url, string $result): void
    {
        $uri = new Uri($url);

        $this->assertSame($result, $uri->getPath());
    }

    /** @return non-empty-list<array{non-empty-string, non-empty-string}> */
    public function utf8PathsDataProvider(): array
    {
        return [
            ['http://example.com/тестовый_путь/', '/тестовый_путь/'],
            ['http://example.com/ουτοπία/', '/ουτοπία/'],
            ["http://example.com/\x21\x92", '/%21%92'],
            ['http://example.com/!?', '/%21'],
        ];
    }

    /**
     * @dataProvider utf8QueryStringsDataProvider
     * @param non-empty-string $url
     * @param non-empty-string $result
     */
    public function testUtf8Query(string $url, string $result): void
    {
        $uri = new Uri($url);

        $this->assertSame($result, $uri->getQuery());
    }

    /** @return non-empty-list<array{non-empty-string, non-empty-string}> */
    public function utf8QueryStringsDataProvider(): array
    {
        return [
            ['http://example.com/?q=тестовый_путь', 'q=тестовый_путь'],
            ['http://example.com/?q=ουτοπία', 'q=ουτοπία'],
            ["http://example.com/?q=\x21\x92", 'q=!%92'],
        ];
    }

    public function testUriDoesNotAppendColonToHostIfPortIsEmpty(): void
    {
        $uri = (new Uri())->withHost('google.com');
        $this->assertSame('//google.com', (string) $uri);
    }

    public function testAuthorityIsPrefixedByDoubleSlashIfPresent(): void
    {
        $uri = (new Uri())->withHost('example.com');
        $this->assertSame('//example.com', (string) $uri);
    }

    public function testReservedCharsInPathUnencoded(): void
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('api.linkedin.com')
            ->withPath('/v1/people/~:(first-name,last-name,email-address,picture-url)');

        $this->assertStringContainsString(
            '/v1/people/~:(first-name,last-name,email-address,picture-url)',
            (string) $uri
        );
    }

    public function testHostIsLowercase(): void
    {
        $uri = new Uri('http://HOST.LOC/path?q=1');
        $this->assertSame('host.loc', $uri->getHost());
    }

    public function testHostIsLowercaseWhenIsSetViwWithHost(): void
    {
        $uri = (new Uri())->withHost('NEW-HOST.COM');
        $this->assertSame('new-host.com', $uri->getHost());
    }

    public function testUriDistinguishZeroFromEmptyString(): void
    {
        $expected = 'https://0:0@0:1/0?0#0';
        $uri      = new Uri($expected);
        $this->assertSame($expected, (string) $uri);
    }
}
