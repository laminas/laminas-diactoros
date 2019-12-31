<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\Integration;

use Http\Factory\Diactoros\RequestFactory;
use Http\Psr7Test\ResponseIntegrationTest;
use Laminas\Diactoros\Response;

class ResponseTest extends ResponseIntegrationTest
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
        return new Response();
    }
}
