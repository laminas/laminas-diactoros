<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\UploadedFileIntegrationTest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

use const UPLOAD_ERR_OK;

final class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject(): UploadedFile
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write('foobar');

        return new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK);
    }
}
