<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class ServerRequestTest extends TestCase
{
    private ServerRequest $request;

    protected function setUp(): void
    {
        $this->request = new ServerRequest();
    }

    public function testServerParamsAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getServerParams());
    }

    public function testQueryParamsAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getQueryParams());
    }

    public function testQueryParamsMutatorReturnsCloneWithChanges(): void
    {
        $value   = ['foo' => 'bar'];
        $request = $this->request->withQueryParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getQueryParams());
    }

    public function testCookiesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getCookieParams());
    }

    public function testCookiesMutatorReturnsCloneWithChanges(): void
    {
        $value   = ['foo' => 'bar'];
        $request = $this->request->withCookieParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getCookieParams());
    }

    public function testUploadedFilesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getUploadedFiles());
    }

    public function testParsedBodyIsEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getParsedBody());
    }

    public function testParsedBodyMutatorReturnsCloneWithChanges(): void
    {
        $value   = ['foo' => 'bar'];
        $request = $this->request->withParsedBody($value);
        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getParsedBody());
    }

    public function testAttributesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getAttributes());
    }

    public function testSingleAttributesWhenEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getAttribute('does-not-exist'));
    }

    /**
     * @depends testAttributesAreEmptyByDefault
     */
    public function testAttributeMutatorReturnsCloneWithChanges(): ServerRequest
    {
        $request = $this->request->withAttribute('foo', 'bar');
        $this->assertNotSame($this->request, $request);
        $this->assertSame('bar', $request->getAttribute('foo'));
        return $request;
    }

    /**
     * @depends testAttributeMutatorReturnsCloneWithChanges
     */
    public function testRemovingAttributeReturnsCloneWithoutAttribute(ServerRequest $request): void
    {
        $new = $request->withoutAttribute('foo');
        $this->assertNotSame($request, $new);
        $this->assertNull($new->getAttribute('foo', null));
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string|null, non-empty-string}> */
    public function provideMethods(): array
    {
        return [
            'post' => ['POST', 'POST'],
            'get'  => ['GET', 'GET'],
            'null' => [null, 'GET'],
        ];
    }

    /**
     * @dataProvider provideMethods
     * @param non-empty-string|null $parameterMethod
     * @param non-empty-string $methodReturned
     */
    public function testUsesProvidedConstructorArguments(?string $parameterMethod, string $methodReturned): void
    {
        $server = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];

        $server['server'] = true;

        $files = [
            'files' => new UploadedFile('php://temp', 0, 0),
        ];

        $uri         = new Uri('http://example.com');
        $headers     = [
            'host' => ['example.com'],
        ];
        $cookies     = [
            'boo' => 'foo',
        ];
        $queryParams = [
            'bar' => 'bat',
        ];
        $parsedBody  = 'bazbar';
        $protocol    = '1.2';

        $request = new ServerRequest(
            $server,
            $files,
            $uri,
            $parameterMethod,
            'php://memory',
            $headers,
            $cookies,
            $queryParams,
            $parsedBody,
            $protocol
        );

        $this->assertSame($server, $request->getServerParams());
        $this->assertSame($files, $request->getUploadedFiles());

        $this->assertSame($uri, $request->getUri());
        $this->assertSame($methodReturned, $request->getMethod());
        $this->assertSame($headers, $request->getHeaders());
        $this->assertSame($cookies, $request->getCookieParams());
        $this->assertSame($queryParams, $request->getQueryParams());
        $this->assertSame($parsedBody, $request->getParsedBody());
        $this->assertSame($protocol, $request->getProtocolVersion());

        $body = $request->getBody();
        $r    = new ReflectionProperty($body, 'stream');
        $r->setAccessible(true);
        $stream = $r->getValue($body);
        $this->assertSame('php://memory', $stream);
    }

    /**
     * @group 46
     */
    public function testCookieParamsAreAnEmptyArrayAtInitialization(): void
    {
        $request = new ServerRequest();
        $this->assertIsArray($request->getCookieParams());
        $this->assertCount(0, $request->getCookieParams());
    }

    /**
     * @group 46
     */
    public function testQueryParamsAreAnEmptyArrayAtInitialization(): void
    {
        $request = new ServerRequest();
        $this->assertIsArray($request->getQueryParams());
        $this->assertCount(0, $request->getQueryParams());
    }

    /**
     * @group 46
     */
    public function testParsedBodyIsNullAtInitialization(): void
    {
        $request = new ServerRequest();
        $this->assertNull($request->getParsedBody());
    }

    public function testAllowsRemovingAttributeWithNullValue(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('boo', null);
        $request = $request->withoutAttribute('boo');
        $this->assertSame([], $request->getAttributes());
    }

    public function testAllowsRemovingNonExistentAttribute(): void
    {
        $request = new ServerRequest();
        $request = $request->withoutAttribute('boo');
        $this->assertSame([], $request->getAttributes());
    }

    public function testTryToAddInvalidUploadedFiles(): void
    {
        $request = new ServerRequest();

        $this->expectException(InvalidArgumentException::class);

        $request->withUploadedFiles([null]);
    }

    public function testNestedUploadedFiles(): void
    {
        $request = new ServerRequest();

        $uploadedFiles = [
            [
                new UploadedFile('php://temp', 0, 0),
                new UploadedFile('php://temp', 0, 0),
            ],
        ];

        $request = $request->withUploadedFiles($uploadedFiles);

        $this->assertSame($uploadedFiles, $request->getUploadedFiles());
    }
}
