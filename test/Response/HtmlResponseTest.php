<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use InvalidArgumentException;
use Laminas\Diactoros\Response\HtmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class HtmlResponseTest extends TestCase
{
    public function testConstructorAcceptsHtmlString(): void
    {
        $body = '<html>Uh oh not found</html>';

        $response = new HtmlResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $body   = '<html>Uh oh not found</html>';
        $status = 404;

        $response = new HtmlResponse($body, $status);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $body    = '<html>Uh oh not found</html>';
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];

        $response = new HtmlResponse($body, $status, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $body     = $this->createStub(StreamInterface::class);
        $response = new HtmlResponse($body);
        $this->assertSame($body, $response->getBody());
    }

    /** @return array<non-empty-string, array{mixed}> */
    public function invalidHtmlContent(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['php://temp']],
            'object'     => [(object) ['php://temp']],
        ];
    }

    /**
     * @dataProvider invalidHtmlContent
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent(mixed $body): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        new HtmlResponse($body);
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $html     = '<p>test data</p>';
        $response = new HtmlResponse($html);

        $actual = $response->getBody()->getContents();
        $this->assertSame($html, $actual);
    }
}
