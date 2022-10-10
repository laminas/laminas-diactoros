<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use InvalidArgumentException;
use Laminas\Diactoros\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

use const PHP_EOL;

class XmlResponseTest extends TestCase
{
    public function testConstructorAcceptsBodyAsString(): void
    {
        $body = 'Super valid XML';

        $response = new XmlResponse($body);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $body   = 'More valid XML';
        $status = 404;

        $response = new XmlResponse($body, $status);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $body    = '<nearly>Valid XML</nearly>';
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];

        $response = new XmlResponse($body, $status, $headers);
        $this->assertSame(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertSame('application/xml; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame($body, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $body     = $this->createMock(StreamInterface::class);
        $response = new XmlResponse($body);
        $this->assertSame($body, $response->getBody());
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
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
     */
    public function testRaisesExceptionforNonStringNonStreamBodyContent(mixed $body): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        new XmlResponse($body);
    }

    /**
     * @group 115
     */
    public function testConstructorRewindsBodyStream(): void
    {
        $body     = '<?xml version="1.0"?>' . PHP_EOL . '<something>Valid XML</something>';
        $response = new XmlResponse($body);

        $actual = $response->getBody()->getContents();
        $this->assertSame($body, $actual);
    }
}
