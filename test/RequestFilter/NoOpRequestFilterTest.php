<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\RequestFilter;

use Laminas\Diactoros\RequestFilter\NoOpRequestFilter;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

class NoOpRequestFilterTest extends TestCase
{
    public function testReturnsSameInstanceItWasProvided(): void
    {
        $request = new ServerRequest();
        $filter  = new NoOpRequestFilter();

        $this->assertSame($request, $filter->filterRequest($request));
    }
}
