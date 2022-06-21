<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Psr\Http\Message\ServerRequestInterface;

final class NoOpRequestFilter implements ServerRequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request;
    }
}
