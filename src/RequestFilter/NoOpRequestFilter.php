<?php

declare(strict_types=1);

namespace Laminas\Diactoros\RequestFilter;

use Psr\Http\Message\ServerRequestInterface;

final class NoOpRequestFilter implements RequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request;
    }
}
