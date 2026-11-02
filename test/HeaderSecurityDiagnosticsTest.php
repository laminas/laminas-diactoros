<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\HeaderSecurity;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;

use function restore_error_handler;
use function set_error_handler;

final class HeaderSecurityDiagnosticsTest extends TestCase
{
    /** HeaderSecurity::filter() must not read past the end of the string on a trailing CR. */
    public function testFilterHandlesTrailingCarriageReturnWithoutDiagnostics(): void
    {
        $errors = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = $errstr;
            return true;
        });
        try {
            $filtered = HeaderSecurity::filter("value\r");
        } finally {
            restore_error_handler();
        }

        $this->assertSame('value', $filtered);
        $this->assertSame([], $errors, 'filter() emitted PHP diagnostics');
    }

    /** The rejection message must not contain raw control characters from the rejected value. */
    public function testInvalidHeaderValueExceptionMessageDoesNotEchoControlCharacters(): void
    {
        try {
            (new Response())->withHeader('X-Foo', "a\r\nsecond line");
        } catch (InvalidArgumentException $e) {
            $this->assertDoesNotMatchRegularExpression('/[\r\n]/', $e->getMessage());
            return;
        }

        $this->fail('Invalid header value was accepted');
    }

    /** The rejection message must not contain raw control characters from the rejected name. */
    public function testInvalidHeaderNameExceptionMessageDoesNotEchoControlCharacters(): void
    {
        try {
            (new Response())->withHeader("X-Foo\r\nsecond line", 'value');
        } catch (InvalidArgumentException $e) {
            $this->assertDoesNotMatchRegularExpression('/[\r\n]/', $e->getMessage());
            return;
        }

        $this->fail('Invalid header name was accepted');
    }
}
