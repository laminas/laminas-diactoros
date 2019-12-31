<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\SapiEmitter;
use LaminasTest\Diactoros\TestAsset\HeaderStack;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class SapiEmitterTest extends TestCase
{
    /**
     * @var SapiEmitter
     */
    protected $emitter;

    public function setUp()
    {
        HeaderStack::reset();
        $this->emitter = new SapiEmitter();
    }

    public function tearDown()
    {
        HeaderStack::reset();
    }

    public function testEmitsResponseHeaders()
    {
        $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Content-Type', 'text/plain');
        $response->getBody()->write('Content!');

        ob_start();
        $this->emitter->emit($response);
        ob_end_clean();
        $this->assertContains('HTTP/1.1 200 OK', HeaderStack::stack());
        $this->assertContains('Content-Type: text/plain', HeaderStack::stack());
        $this->assertContains('Content-Length: 8', HeaderStack::stack());
    }

    public function testEmitsMessageBody()
    {
        $response = (new Response())
            ->withStatus(200)
            ->withAddedHeader('Content-Type', 'text/plain');
        $response->getBody()->write('Content!');

        $this->expectOutputString('Content!');
        $this->emitter->emit($response);
    }

    public function testDoesNotInjectContentLengthHeaderIfStreamSizeIsUnknown()
    {
        $stream = $this->prophesize('Psr\Http\Message\StreamInterface');
        $stream->__toString()->willReturn('Content!');
        $stream->getSize()->willReturn(null);
        $response = (new Response())
            ->withStatus(200)
            ->withBody($stream->reveal());

        ob_start();
        $this->emitter->emit($response);
        ob_end_clean();
        foreach (HeaderStack::stack() as $header) {
            $this->assertNotContains('Content-Length:', $header);
        }
    }
}
