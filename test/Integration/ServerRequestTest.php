<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ServerRequest;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        return new ServerRequest($_SERVER);
    }
}
