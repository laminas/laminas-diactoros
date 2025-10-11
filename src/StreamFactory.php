<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Override;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

use function assert;
use function fopen;
use function fwrite;
use function is_resource;
use function rewind;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        assert(is_resource($resource), 'Something is really wrong if PHP failed to open stream in memory');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new Stream($filename, $mode);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
