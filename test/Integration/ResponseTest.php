<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Laminas\Diactoros\Response;
use Override;

final class ResponseTest extends ResponseIntegrationTest
{
    #[Override]
    public function createSubject(): Response
    {
        return new Response();
    }
}
