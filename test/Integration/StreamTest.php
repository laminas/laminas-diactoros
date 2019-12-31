<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\StreamIntegrationTest;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($data)
    {
        if ($data instanceof StreamInterface) {
            return $data;
        }

        return new Stream($data);
    }
}
