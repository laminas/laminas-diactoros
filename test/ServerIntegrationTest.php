<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\Response\SapiStreamEmitter;
use Laminas\Diactoros\Server;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerIntegrationTest extends TestCase
{
    public function testPassesBufferLevelToSapiStreamEmitter()
    {
        $currentObLevel = ob_get_level();
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $emitter = $this->prophesize(SapiStreamEmitter::class);
        $emitter
            ->emit(
                $response,
                $currentObLevel + 1
            )
            ->shouldBeCalled();

        $middleware = function ($req, $res) use ($request, $response) {
            TestCase::assertSame($request, $req);
            TestCase::assertSame($response, $res);
            return $res;
        };

        $server = new Server(
            $middleware,
            $request,
            $response
        );
        $server->setEmitter($emitter->reveal());
        $server->listen();

        ob_end_clean();
    }
}
