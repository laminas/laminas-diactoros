<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use GdImage;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

use function fopen;
use function fwrite;
use function rewind;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
    {
        return new Stream($file, $mode);
    }

    /**
     * {@inheritDoc}
     *
     * @param resource|GdImage $resource
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if ($resource instanceof GdImage) {
            return new ImageStream($resource);
        }

        return new Stream($resource);
    }
}
