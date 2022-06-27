<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ServerRequestFilter\NoOpRequestFilter;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class NoOpRequestFilterTest extends TestCase
{
    public function testReturnsSameInstanceItWasProvided(): void
    {
        $request = new ServerRequest();
        $filter  = new NoOpRequestFilter();

        $this->assertSame($request, $filter($request));
    }
}
