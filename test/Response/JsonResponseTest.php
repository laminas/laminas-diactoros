<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Response;

use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\TestCase;
use stdClass;

use function fopen;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class JsonResponseTest extends TestCase
{
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody(): void
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
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));
        $this->assertSame($json, (string) $response->getBody());
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function scalarValuesForJSON()
    {
        return [
            'null'         => [null],
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
    public function testScalarValuePassedToConstructorJsonEncodesDirectly(mixed $value): void
    {
        $response = new JsonResponse($value);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonResponse
        $this->assertSame(json_encode($value, 15), (string) $response->getBody());
    }

    public function testCanProvideStatusCodeToConstructor(): void
    {
        $response = new JsonResponse(null, 404);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCanProvideAlternateContentTypeViaHeadersPassedToConstructor(): void
    {
        $response = new JsonResponse(null, 200, ['content-type' => 'foo/json']);
        $this->assertSame('foo/json', $response->getHeaderLine('content-type'));
    }

    public function testJsonErrorHandlingOfResources(): void
    {
        // Serializing something that is not serializable.
        $resource = fopen('php://memory', 'r');

        $this->expectException(InvalidArgumentException::class);

        new JsonResponse($resource);
    }

    public function testJsonErrorHandlingOfBadEmbeddedData(): void
    {
        // Serializing something that is not serializable.
        $data = [
            'stream' => fopen('php://memory', 'r'),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode');

        new JsonResponse($data);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string}> */
    public function valuesToJsonEncode(): array
    {
        return [
            'uri'    => ['https://example.com/foo?bar=baz&baz=bat', 'uri'],
            'html'   => ['<p class="test">content</p>', 'html'],
            'string' => ["Don't quote!", 'string'],
        ];
    }

    /**
     * @dataProvider valuesToJsonEncode
     * @param non-empty-string $value
     * @param non-empty-string $key
     */
    public function testUsesSaneDefaultJsonEncodingFlags(string $value, string $key): void
    {
        $defaultFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;

        $response = new JsonResponse([$key => $value]);
        $stream   = $response->getBody();
        $contents = (string) $stream;

        $expected = json_encode($value, $defaultFlags);
        $this->assertStringContainsString(
            $expected,
            $contents,
            sprintf('Did not encode %s properly; expected (%s), received (%s)', $key, $expected, $contents)
        );
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $json     = ['test' => 'data'];
        $response = new JsonResponse($json);

        $actual = json_decode($response->getBody()->getContents(), true);
        $this->assertSame($json, $actual);
    }

    public function testPayloadGetter(): void
    {
        $payload  = ['test' => 'data'];
        $response = new JsonResponse($payload);
        $this->assertSame($payload, $response->getPayload());
    }

    public function testWithPayload(): void
    {
        $response    = new JsonResponse(['test' => 'data']);
        $json        = ['foo' => 'bar'];
        $newResponse = $response->withPayload($json);
        $this->assertNotSame($response, $newResponse);

        $this->assertSame($json, $newResponse->getPayload());
        $decodedBody = json_decode($newResponse->getBody()->getContents(), true);
        $this->assertSame($json, $decodedBody);
    }

    public function testEncodingOptionsGetter(): void
    {
        $response = new JsonResponse([]);
        $this->assertSame(JsonResponse::DEFAULT_JSON_FLAGS, $response->getEncodingOptions());
    }

    public function testWithEncodingOptions(): void
    {
        $response = new JsonResponse(['foo' => 'bar']);
        $expected = <<<JSON
            {"foo":"bar"}
            JSON;

        $this->assertSame($expected, $response->getBody()->getContents());

        $newResponse = $response->withEncodingOptions(JSON_PRETTY_PRINT);

        $this->assertNotSame($response, $newResponse);

        $expected = <<<JSON
            {
                "foo": "bar"
            }
            JSON;

        $this->assertSame($expected, $newResponse->getBody()->getContents());
    }

    public function testModifyingThePayloadDoesntMutateResponseInstance(): void
    {
        $payload      = new stdClass();
        $payload->foo = 'bar';

        $response = new JsonResponse($payload);

        $originalPayload = clone $payload;
        $payload->bar    = 'baz';

        $this->assertEquals($originalPayload, $response->getPayload());
        $this->assertNotSame($payload, $response->getPayload());
    }
}
