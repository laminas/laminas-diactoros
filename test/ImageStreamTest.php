<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use GdImage;
use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\ImageStream;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;

use function assert;
use function fopen;
use function imagecreate;

class ImageStreamTest extends TestCase
{
    public function testCanInstantiateWithGDResource(): ImageStream
    {
        $resource = imagecreate(1, 1);
        $stream   = new ImageStream($resource);
        $this->assertInstanceOf(ImageStream::class, $stream);

        return $stream;
    }

    /** @depends testCanInstantiateWithGDResource */
    public function testImageStreamExtendsStream(ImageStream $stream): void
    {
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testDetachReturnsGDResource(): void
    {
        $resource = imagecreate(1, 1);
        assert($resource instanceof GdImage);
        $stream   = new ImageStream($resource);
        $detached = $stream->detach();

        $this->assertInstanceOf(GdImage::class, $detached);
        $this->assertSame($resource, $detached);
    }

    /** @psalm-return non-empty-array<non-empty-string, array{resource}> */
    public function invalidResourceProvider(): array
    {
        return [
            'file' => [fopen(__FILE__, 'r')],
        ];
    }

    /**
     * @dataProvider invalidResourceProvider
     * @param resource $resourceToAttach
     */
    public function testAttachRaisesExceptionForNonGDResource($resourceToAttach): void
    {
        $resource = imagecreate(1, 1);
        assert($resource instanceof GdImage);

        $stream = new ImageStream($resource);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resource must be a GdImage');
        $stream->attach($resourceToAttach);
    }

    public function testAttachSwitchesToNewResourceWhenSuccessful(): void
    {
        $resource1 = imagecreate(1, 1);
        assert($resource1 instanceof GdImage);
        $resource2 = imagecreate(1, 1);
        assert($resource2 instanceof GdImage);

        $stream = new ImageStream($resource1);
        $stream->attach($resource2);

        $detached = $stream->detach();

        $this->assertInstanceOf(GdImage::class, $detached);
        $this->assertSame($resource2, $detached);
    }
}
