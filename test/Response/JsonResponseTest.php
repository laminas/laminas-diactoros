<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\Response;

use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit_Framework_TestCase as TestCase;

class JsonResponseTest extends TestCase
{
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody()
    {
        $data = [
            'nested' => [
                'json' => [
                    'tree',
                ],
            ],
        ];
        $json = '{"nested":{"json":["tree"]}}';

        $response = new JsonResponse($data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertSame($json, (string) $response->getBody());
    }

    public function testNullValuePassedToConstructorRendersEmptyJsonObjectInBody()
    {
        $response = new JsonResponse(null);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertSame('{}', (string) $response->getBody());
    }

    public function scalarValuesForJSON()
    {
        return [
            'false'        => [false],
            'true'         => [true],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'empty-string' => [''],
            'string'       => ['string'],
        ];
    }

    /**
     * @dataProvider scalarValuesForJSON
     */
    public function testScalarValuePassedToConstructorRendersValueWithinJSONArray($value)
    {
        $response = new JsonResponse($value);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertSame(json_encode([$value], JSON_UNESCAPED_SLASHES), (string) $response->getBody());
    }

    public function testCanProvideStatusCodeToConstructor()
    {
        $response = new JsonResponse(null, 404);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCanProvideAlternateContentTypeViaHeadersPassedToConstructor()
    {
        $response = new JsonResponse(null, 200, ['content-type' => 'foo/json']);
        $this->assertEquals('foo/json', $response->getHeaderLine('content-type'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testJsonErrorHandlingOfResources()
    {
        // Serializing something that is not serializable.
        $resource = fopen('php://memory', 'r');
        new JsonResponse($resource);
    }

    public function testJsonErrorHandlingOfBadEmbeddedData()
    {
        if (version_compare(PHP_VERSION, '5.5', 'lt')) {
            $this->markTestSkipped('Skipped as PHP versions prior to 5.5 are noisy about JSON errors');
        }

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Skipped as HHVM happily serializes embedded resources');
        }

        // Serializing something that is not serializable.
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];

        $this->setExpectedException('InvalidArgumentException', 'Unable to encode');
        new JsonResponse($data);
    }
}
