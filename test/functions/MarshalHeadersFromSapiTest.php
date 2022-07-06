<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\functions;

use PHPUnit\Framework\TestCase;

use function Laminas\Diactoros\marshalHeadersFromSapi;

class MarshalHeadersFromSapiTest extends TestCase
{
    public function testReturnsHeaders(): void
    {
        $server = [
            'REDIRECT_CONTENT_FOO' => 'redirect-foo',
            'CONTENT_FOO'          => null,
            'REDIRECT_CONTENT_BAR' => 'redirect-bar',
            'CONTENT_BAR'          => '',
            'REDIRECT_CONTENT_BAZ' => 'redirect-baz',
            'CONTENT_BAZ'          => 'baz',
            'REDIRECT_CONTENT_VAR' => 'redirect-var',
            'REDIRECT_HTTP_ABC'    => 'redirect-abc',
            'HTTP_ABC'             => null,
            'REDIRECT_HTTP_DEF'    => 'redirect-def',
            'HTTP_DEF'             => '',
            'REDIRECT_HTTP_GHI'    => 'redirect-ghi',
            'HTTP_GHI'             => 'ghi',
            'REDIRECT_HTTP_JKL'    => 'redirect-jkl',
            'HTTP_TEST_MNO'        => 'mno',
            'HTTP_TEST_PQR'        => '',
            'HTTP_TEST_STU'        => null,
            'CONTENT_TEST_VW'      => 'vw',
            'CONTENT_TEST_XY'      => '',
            'CONTENT_TEST_ZZ'      => null,
            123                    => 'integer',
        ];

        $expectedHeaders = [
            'content-foo'     => null,
            'content-baz'     => 'baz',
            'content-var'     => 'redirect-var',
            'abc'             => null,
            'ghi'             => 'ghi',
            'jkl'             => 'redirect-jkl',
            'test-mno'        => 'mno',
            'test-stu'        => null,
            'content-test-vw' => 'vw',
            'content-test-zz' => null,
        ];

        $headers = marshalHeadersFromSapi($server);

        self::assertSame($expectedHeaders, $headers);
    }
}
