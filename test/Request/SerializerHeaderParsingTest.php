<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Request;

use Laminas\Diactoros\Exception\DeserializationException;
use Laminas\Diactoros\Request\Serializer as RequestSerializer;
use PHPUnit\Framework\TestCase;

final class SerializerHeaderParsingTest extends TestCase
{
    /**
     * A header line consisting of "0" is falsy and must not end header parsing in
     * AbstractSerializer::splitStream(); otherwise subsequent header lines become body.
     */
    public function testSerializerDoesNotStopHeaderParsingOnFalsyLine(): void
    {
        try {
            $request = RequestSerializer::fromString(
                "GET / HTTP/1.1\r\nHost: example.com\r\n0\r\nX-Other: 1\r\n\r\n"
            );
        } catch (DeserializationException $e) {
            $this->assertNotSame('', $e->getMessage());
            return;
        }

        $this->assertSame('', (string) $request->getBody(), 'Header lines leaked into the body');
    }
}
