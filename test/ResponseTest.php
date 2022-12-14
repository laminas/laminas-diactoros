<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use DOMDocument;
use DOMXPath;
use InvalidArgumentException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;

use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function file_exists;
use function file_put_contents;
use function getenv;
use function gmdate;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function strtotime;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_USERAGENT;
use const LOCK_EX;

final class ResponseTest extends TestCase
{
    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response();
    }

    public function testStatusCodeIs200ByDefault(): void
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    public function testStatusCodeMutatorReturnsCloneWithChanges(): void
    {
        $response = $this->response->withStatus(400);
        $this->assertNotSame($this->response, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testReasonPhraseDefaultsToStandards(): void
    {
        $response = $this->response->withStatus(422);
        $this->assertSame('Unprocessable Content', $response->getReasonPhrase());
    }

    private function fetchIanaStatusCodes(): DOMDocument
    {
        $updated                 = null;
        $ianaHttpStatusCodesFile = __DIR__ . '/TestAsset/.cache/http-status-codes.xml';
        $ianaHttpStatusCodes     = null;
        if (file_exists($ianaHttpStatusCodesFile)) {
            $ianaHttpStatusCodes = new DOMDocument();
            $ianaHttpStatusCodes->load($ianaHttpStatusCodesFile);
            if (! $ianaHttpStatusCodes->relaxNGValidate(__DIR__ . '/TestAsset/http-status-codes.rng')) {
                $ianaHttpStatusCodes = null;
            }
        }
        if ($ianaHttpStatusCodes) {
            if (! getenv('ALWAYS_REFRESH_IANA_HTTP_STATUS_CODES')) {
                // use cached codes
                return $ianaHttpStatusCodes;
            }
            $xpath = new DOMXPath($ianaHttpStatusCodes);
            $xpath->registerNamespace('ns', 'http://www.iana.org/assignments');

            $updatedQueryResult = $xpath->query('//ns:updated');
            if ($updatedQueryResult !== false && $updatedQueryResult->length > 0) {
                $updated = $updatedQueryResult->item(0)?->nodeValue ?: '';
                $updated = strtotime($updated);
            }
        }

        $ch = curl_init('https://www.iana.org/assignments/http-status-codes/http-status-codes.xml');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Curl');
        if ($updated) {
            $ifModifiedSince = sprintf(
                'If-Modified-Since: %s',
                gmdate('D, d M Y H:i:s \G\M\T', $updated)
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$ifModifiedSince]);
        }
        $response     = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseCode === 304 && $ianaHttpStatusCodes) {
            // status codes did not change
            return $ianaHttpStatusCodes;
        }

        if ($responseCode === 200 && is_string($response) && $response !== '') {
            $downloadedIanaHttpStatusCodes = new DOMDocument();
            $downloadedIanaHttpStatusCodes->loadXML($response);
            if ($downloadedIanaHttpStatusCodes->relaxNGValidate(__DIR__ . '/TestAsset/http-status-codes.rng')) {
                file_put_contents($ianaHttpStatusCodesFile, $response, LOCK_EX);
                return $downloadedIanaHttpStatusCodes;
            }
        }
        if ($ianaHttpStatusCodes) {
            // return cached codes if available
            return $ianaHttpStatusCodes;
        }
        self::fail('Unable to retrieve IANA response status codes due to timeout or invalid XML');
    }

    /** @return list<array{numeric-string, non-empty-string}> */
    public function ianaCodesReasonPhrasesProvider(): array
    {
        $ianaHttpStatusCodes = $this->fetchIanaStatusCodes();

        $ianaCodesReasonPhrases = [];

        $xpath = new DOMXPath($ianaHttpStatusCodes);
        $xpath->registerNamespace('ns', 'http://www.iana.org/assignments');

        $records = $xpath->query('//ns:record');

        foreach ($records as $record) {
            $valueQueryResult       = $xpath->query('.//ns:value', $record);
            $descriptionQueryResult = $xpath->query('.//ns:description', $record);

            if (false === $valueQueryResult || false === $descriptionQueryResult) {
                continue;
            }

            $value       = $valueQueryResult->item(0)?->nodeValue ?: '';
            $description = $descriptionQueryResult->item(0)?->nodeValue ?: '';

            if (in_array($description, ['Unassigned', '(Unused)'], true)) {
                continue;
            }

            $value       = $value;
            $description = $description;

            if (preg_match('/^([0-9]+)\s*\-\s*([0-9]+)$/', $value, $matches)) {
                for ($value = $matches[1]; $value <= $matches[2]; $value++) {
                    $ianaCodesReasonPhrases[] = [$value, $description];
                }
            } else {
                $ianaCodesReasonPhrases[] = [$value, $description];
            }
        }

        return $ianaCodesReasonPhrases;
    }

    /**
     * @dataProvider ianaCodesReasonPhrasesProvider
     * @param numeric-string $code
     * @param non-empty-string $reasonPhrase
     */
    public function testReasonPhraseDefaultsAgainstIana(string $code, string $reasonPhrase): void
    {
        /** @psalm-suppress InvalidArgument */
        $response = $this->response->withStatus($code);
        $this->assertSame($reasonPhrase, $response->getReasonPhrase());
    }

    public function testCanSetCustomReasonPhrase(): void
    {
        $response = $this->response->withStatus(422, 'Foo Bar!');
        $this->assertSame('Foo Bar!', $response->getReasonPhrase());
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidReasonPhrases(): array
    {
        return [
            'true'    => [true],
            'false'   => [false],
            'array'   => [[200]],
            'object'  => [(object) ['reasonPhrase' => 'Ok']],
            'integer' => [99],
            'float'   => [400.5],
            'null'    => [null],
        ];
    }

    /**
     * @dataProvider invalidReasonPhrases
     */
    public function testWithStatusRaisesAnExceptionForNonStringReasonPhrases(mixed $invalidReasonPhrase): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        $this->response->withStatus(422, $invalidReasonPhrase);
    }

    public function testConstructorRaisesExceptionForInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        new Response(['TOTALLY INVALID']);
    }

    public function testConstructorCanAcceptAllMessageParts(): void
    {
        $body    = new Stream('php://memory');
        $status  = 302;
        $headers = [
            'location' => ['http://example.com/'],
        ];

        $response = new Response($body, $status, $headers);
        $this->assertSame($body, $response->getBody());
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($headers, $response->getHeaders());
    }

    /**
     * @dataProvider validStatusCodes
     * @param int|numeric-string $code
     */
    public function testCreateWithValidStatusCodes($code): void
    {
        /** @psalm-suppress PossiblyInvalidArgument */
        $response = $this->response->withStatus($code);

        $result = $response->getStatusCode();

        $this->assertSame((int) $code, $result);
        $this->assertIsInt($result);
    }

    /** @return non-empty-array<non-empty-string, array{int|numeric-string}> */
    public function validStatusCodes(): array
    {
        return [
            'minimum'        => [100],
            'middle'         => [300],
            'string-integer' => ['300'],
            'maximum'        => [599],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testCannotSetInvalidStatusCode(mixed $code): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress MixedArgument */
        $this->response->withStatus($code);
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidStatusCodes(): array
    {
        return [
            'true'     => [true],
            'false'    => [false],
            'array'    => [[200]],
            'object'   => [(object) ['statusCode' => 200]],
            'too-low'  => [99],
            'float'    => [400.5],
            'too-high' => [600],
            'null'     => [null],
            'string'   => ['foo'],
        ];
    }

    /** @return non-empty-array<non-empty-string, array{mixed}> */
    public function invalidResponseBody(): array
    {
        return [
            'true'     => [true],
            'false'    => [false],
            'int'      => [1],
            'float'    => [1.1],
            'array'    => [['BODY']],
            'stdClass' => [(object) ['body' => 'BODY']],
        ];
    }

    /**
     * @dataProvider invalidResponseBody
     */
    public function testConstructorRaisesExceptionForInvalidBody(mixed $body): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');

        /** @psalm-suppress MixedArgument */
        new Response($body);
    }

    /** @return non-empty-array<non-empty-string, array{0: array<mixed>, 1?: non-empty-string}> */
    public function invalidHeaderTypes(): array
    {
        return [
            'indexed-array' => [[['INVALID']], 'header name'],
            'null'          => [['x-invalid-null' => null]],
            'true'          => [['x-invalid-true' => true]],
            'false'         => [['x-invalid-false' => false]],
            'object'        => [['x-invalid-object' => (object) ['INVALID']]],
        ];
    }

    /**
     * @dataProvider invalidHeaderTypes
     * @group 99
     * @param array<mixed> $headers
     * @param non-empty-string $contains
     */
    public function testConstructorRaisesExceptionForInvalidHeaders(
        array $headers,
        string $contains = 'header value type'
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($contains);

        new Response('php://memory', 200, $headers);
    }

    public function testReasonPhraseCanBeEmpty(): void
    {
        $response = $this->response->withStatus(555);
        $this->assertIsString($response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-string, non-empty-string|non-empty-list<non-empty-string>}> */
    public function headersWithInjectionVectors(): array
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    /**
     * @dataProvider headersWithInjectionVectors
     * @param string|non-empty-list<non-empty-string> $value
     */
    public function testConstructorRaisesExceptionForHeadersWithCRLFVectors(string $name, $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Response('php://memory', 200, [$name => $value]);
    }
}
