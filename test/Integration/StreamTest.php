<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\StreamIntegrationTest;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class StreamTest extends StreamIntegrationTest
{
    /**
     * @param string|resource|StreamInterface $data
     */
    public function createStream($data): StreamInterface
    {
        if ($data instanceof StreamInterface) {
            return $data;
        }

        return new Stream($data);
    }
}
