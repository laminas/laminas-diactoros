<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

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
