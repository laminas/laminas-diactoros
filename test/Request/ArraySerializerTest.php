<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\Request;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Request\ArraySerializer;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PHPUnit_Framework_TestCase as TestCase;

class ArraySerializerTest extends TestCase
{
    public function testSerializeToArray()
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write('{"test":"value"}');

        $request = (new Request())
            ->withMethod('POST')
            ->withUri(new Uri('http://example.com/foo/bar?baz=bat'))
            ->withAddedHeader('Accept', 'application/json')
            ->withAddedHeader('X-Foo-Bar', 'Baz')
            ->withAddedHeader('X-Foo-Bar', 'Bat')
            ->withBody($stream);

        $message = ArraySerializer::toArray($request);

        $this->assertSame([
            'method' => 'POST',
            'request_target' => '/foo/bar?baz=bat',
            'uri' => 'http://example.com/foo/bar?baz=bat',
            'protocol_version' => '1.1',
            'headers' => [
                'Host' => [
                    'example.com',
                ],
                'Accept' => [
                    'application/json',
                ],
                'X-Foo-Bar' => [
                    'Baz',
                    'Bat'
                ],
            ],
            'body' => '{"test":"value"}',
        ], $message);
    }

    public function testDeserializeFromArray()
    {
        $serializedRequest = [
            'method' => 'POST',
            'request_target' => '/foo/bar?baz=bat',
            'uri' => 'http://example.com/foo/bar?baz=bat',
            'protocol_version' => '1.1',
            'headers' => [
                'Host' => [
                    'example.com',
                ],
                'Accept' => [
                    'application/json',
                ],
                'X-Foo-Bar' => [
                    'Baz',
                    'Bat'
                ],
            ],
            'body' => '{"test":"value"}',
        ];

        $message = ArraySerializer::fromArray($serializedRequest);

        $stream = new Stream('php://memory', 'wb+');
        $stream->write('{"test":"value"}');

        $request = (new Request())
            ->withMethod('POST')
            ->withUri(new Uri('http://example.com/foo/bar?baz=bat'))
            ->withAddedHeader('Accept', 'application/json')
            ->withAddedHeader('X-Foo-Bar', 'Baz')
            ->withAddedHeader('X-Foo-Bar', 'Bat')
            ->withBody($stream);

        $this->assertSame(Request\Serializer::toString($request), Request\Serializer::toString($message));
    }

    public function testMissingBodyParamInSerializedRequestThrowsException()
    {
        $serializedRequest = [
            'method' => 'POST',
            'request_target' => '/foo/bar?baz=bat',
            'uri' => 'http://example.com/foo/bar?baz=bat',
            'protocol_version' => '1.1',
            'headers' => [
                'Host' => [
                    'example.com',
                ],
                'Accept' => [
                    'application/json',
                ],
                'X-Foo-Bar' => [
                    'Baz',
                    'Bat'
                ],
            ],
        ];

        $this->setExpectedException('UnexpectedValueException');

        ArraySerializer::fromArray($serializedRequest);
    }
}
