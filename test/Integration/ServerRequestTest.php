<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Laminas\Diactoros\ServerRequest;
use Override;

final class ServerRequestTest extends ServerRequestIntegrationTest
{
    #[Override]
    public function createSubject(): ServerRequest
    {
        return new ServerRequest($_SERVER);
    }
}
