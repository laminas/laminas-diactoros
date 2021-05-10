<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use InvalidArgumentException;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testConstructorAcceptsStringUriAndProduces302ResponseWithLocationHeader()
    {
        $response = new RedirectResponse('/foo/bar');
        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAcceptsUriInstanceAndProduces302ResponseWithLocationHeader()
    {
        $uri = new Uri('https://example.com:10082/foo/bar');
        $response = new RedirectResponse($uri);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame((string) $uri, $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingAlternateStatusCode()
    {
        $response = new RedirectResponse('/foo/bar', 301);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingHeaders()
    {
        $response = new RedirectResponse('/foo/bar', 302, ['X-Foo' => ['Bar']]);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertSame('/foo/bar', $response->getHeaderLine('Location'));
        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertSame('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function invalidUris()
    {
        return [
            'null'       => [ null ],
            'false'      => [ false ],
            'true'       => [ true ],
            'zero'       => [ 0 ],
            'int'        => [ 1 ],
            'zero-float' => [ 0.0 ],
            'float'      => [ 1.1 ],
            'array'      => [ [ '/foo/bar' ] ],
            'object'     => [ (object) [ '/foo/bar' ] ],
        ];
    }

    /**
     * @dataProvider invalidUris
     */
    public function testConstructorRaisesExceptionOnInvalidUri($uri)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Uri');

        new RedirectResponse($uri);
    }
}
