<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\ArraySerializer;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class ArraySerializerTest extends TestCase
{
    public function testSerializeToArray(): void
    {
        $response = $this->createResponse();

        $message = ArraySerializer::toArray($response);

        $this->assertSame($this->createSerializedResponse(), $message);
    }

    public function testDeserializeFromArray(): void
    {
        $serializedResponse = $this->createSerializedResponse();

        $message = ArraySerializer::fromArray($serializedResponse);

        $response = $this->createResponse();

        $this->assertSame(Response\Serializer::toString($response), Response\Serializer::toString($message));
    }

    public function testMissingBodyParamInSerializedRequestThrowsException(): void
    {
        $serializedRequest = $this->createSerializedResponse();
        unset($serializedRequest['body']);

        $this->expectException(UnexpectedValueException::class);

        ArraySerializer::fromArray($serializedRequest);
    }

    private function createResponse(): Response
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write('{"test":"value"}');

        return (new Response())
            ->withStatus(201, 'Custom')
            ->withProtocolVersion('1.1')
            ->withAddedHeader('Accept', 'application/json')
            ->withAddedHeader('X-Foo-Bar', 'Baz')
            ->withAddedHeader('X-Foo-Bar', 'Bat')
            ->withBody($stream);
    }

    /**
     * @return array{
     *     status_code: positive-int,
     *     reason_phrase: non-empty-string,
     *     protocol_version: non-empty-string,
     *     headers: array<non-empty-string, non-empty-list<string>>,
     *     body: string,
     * }
     */
    private function createSerializedResponse(): array
    {
        return [
            'status_code'      => 201,
            'reason_phrase'    => 'Custom',
            'protocol_version' => '1.1',
            'headers'          => [
                'Accept'    => [
                    'application/json',
                ],
                'X-Foo-Bar' => [
                    'Baz',
                    'Bat',
                ],
            ],
            'body'             => '{"test":"value"}',
        ];
    }
}
