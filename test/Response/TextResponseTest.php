<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use InvalidArgumentException;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\StreamInterface;

class TextResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testConstructorAcceptsBodyAsString(): void
    {
        $body = 'Uh oh not found';

        $response = new TextResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $body   = 'Uh oh not found';
        $status = 404;

        $response = new TextResponse($body, $status);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $body    = 'Uh oh not found';
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];

        $response = new TextResponse($body, $status, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream   = $this->prophesize(StreamInterface::class);
        $body     = $stream->reveal();
        $response = new TextResponse($body);
        $this->assertSame($body, $response->getBody());
    }

    /** @return array<string, array{0: mixed}> */
    public function invalidContent(): array
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
     * @dataProvider invalidContent
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        new TextResponse($body);
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $text     = 'test data';
        $response = new TextResponse($text);

        $actual = $response->getBody()->getContents();
        $this->assertSame($text, $actual);
    }
}
