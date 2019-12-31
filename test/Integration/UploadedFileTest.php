<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\Integration;

use Http\Factory\Diactoros\RequestFactory;
use Http\Psr7Test\UploadedFileIntegrationTest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public static function setUpBeforeClass()
    {
        if (! class_exists(RequestFactory::class)) {
            self::markTestSkipped('You need to install http-interop/http-factory-diactoros to run integration tests');
        }
        parent::setUpBeforeClass();
    }

    public function createSubject()
    {
        $stream = new Stream('php://memory', 'rw');
        $stream->write('foobar');

        return new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK);
    }
}
