<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFilter\DoNotFilter;
use PHPUnit\Framework\TestCase;

class DoNotFilterTest extends TestCase
{
    public function testReturnsSameInstanceItWasProvided(): void
    {
        $request = new ServerRequest();
        $filter  = new DoNotFilter();

        $this->assertSame($request, $filter($request));
    }
}
