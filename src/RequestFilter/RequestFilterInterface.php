<?php

declare(strict_types=1);

namespace Laminas\Diactoros\RequestFilter;

use Psr\Http\Message\ServerRequestInterface;

interface RequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface;
}
