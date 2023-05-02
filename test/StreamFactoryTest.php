<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use GdImage;
use Laminas\Diactoros\ImageStream;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;

use function assert;
use function fopen;
use function imagecreate;

class StreamFactoryTest extends TestCase
{
    public function testPassingGdImageToCreateStreamFromResourceReturnsImageStream(): void
    {
        $factory  = new StreamFactory();
        $resource = imagecreate(1, 1);
        assert($resource instanceof GdImage);

        $stream = $factory->createStreamFromResource($resource);
        $this->assertInstanceOf(ImageStream::class, $stream);
    }

    public function testPassingFileResourceToCreateStreamFromResourceReturnsStream(): void
    {
        $factory  = new StreamFactory();
        $resource = fopen(__FILE__, 'r');
        $stream   = $factory->createStreamFromResource($resource);
        $this->assertInstanceOf(Stream::class, $stream);
    }
}
