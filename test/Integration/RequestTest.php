<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Laminas\Diactoros\Request;
use Laminas\Diactoros\RequestFactory;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request('/', 'GET');
    }
}
