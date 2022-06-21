<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface;
}
