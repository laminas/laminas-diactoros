<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\UploadedFileIntegrationTest;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write('foobar');

        return new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK);
    }
}
