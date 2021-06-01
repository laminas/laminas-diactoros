<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Laminas\Diactoros\ServerRequestFactory;

class ServerRequestFactoryTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/', $_SERVER);
    }
}
