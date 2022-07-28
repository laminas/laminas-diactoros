<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\StaticAnalysis;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestInterfaceStaticReturnTypes
{
    public function changeMethodOfServerRequest(ServerRequest $request): ServerRequestInterface
    {
        return $request->withMethod('GET');
    }

    public function changeRequestTargetOfServerRequest(ServerRequest $request): ServerRequestInterface
    {
        return $request->withRequestTarget('foo');
    }

    public function changeUriOfServerRequest(ServerRequest $request): ServerRequestInterface
    {
        return $request->withUri(new Uri('/there'));
    }

    public function changeMethodOfRequest(Request $request): RequestInterface
    {
        return $request->withMethod('GET');
    }

    public function changeRequestTargetOfRequest(Request $request): RequestInterface
    {
        return $request->withRequestTarget('foo');
    }

    public function changeUriOfRequest(Request $request): RequestInterface
    {
        return $request->withUri(new Uri('/there'));
    }
}
