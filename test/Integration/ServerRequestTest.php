<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Laminas\Diactoros\ServerRequest;

final class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject(): ServerRequest
    {
        return new ServerRequest($_SERVER);
    }
}
