<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

final class NoOpRequestFilterFactory
{
    public function __invoke(): NoOpRequestFilter
    {
        return new NoOpRequestFilter();
    }
}
