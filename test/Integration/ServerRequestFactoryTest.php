<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use Laminas\Diactoros\ServerRequestFactory;
use Override;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactoryTest extends ServerRequestIntegrationTest
{
    #[Override]
    public function createSubject(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest('GET', '/', $_SERVER);
    }
}
