<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\TestCase;

class EmptyResponseTest extends TestCase
{
    public function testConstructor()
    {
        $response = new EmptyResponse(201);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testHeaderConstructor()
    {
        $response = EmptyResponse::withHeaders(['x-empty' => ['true']]);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('true', $response->getHeaderLine('x-empty'));
    }
}
