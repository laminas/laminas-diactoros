<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Laminas\Diactoros\Request;
use Override;

final class RequestTest extends RequestIntegrationTest
{
    #[Override]
    public function createSubject(): Request
    {
        return new Request('/', 'GET');
    }
}
