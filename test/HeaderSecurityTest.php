<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\HeaderSecurity;
use PHPUnit\Framework\TestCase;

class HeaderSecurityTest extends TestCase
{
    /**
     * Data for filter value
     *
     * @return array<int, array{0: string, 1: string}>
     */
    public function getFilterValues(): array
    {
        return [
            ["This is a\n test", "This is a test"],
            ["This is a\r test", "This is a test"],
            ["This is a\n\r test", "This is a test"],
            ["This is a\r\n  test", "This is a\r\n  test"],
            ["This is a \r\ntest", "This is a test"],
            ["This is a \r\n\n test", "This is a  test"],
            ["This is a\n\n test", "This is a test"],
            ["This is a\r\r test", "This is a test"],
            ["This is a \r\r\n test", "This is a \r\n test"],
            ["This is a \r\n\r\ntest", "This is a test"],
            ["This is a \r\n\n\r\n test", "This is a \r\n test"],
        ];
    }

    /**
     * @dataProvider getFilterValues
     */
    public function testFiltersValuesPerRfc7230(string $value, string $expected): void
    {
        $this->assertSame($expected, HeaderSecurity::filter($value));
    }

    /** @return array<int, array{0: string, 1: string}> */
    public function validateValues(): array
    {
        return [
            ["This is a\n test", 'assertFalse'],
            ["This is a\r test", 'assertFalse'],
            ["This is a\n\r test", 'assertFalse'],
            ["This is a\r\n  test", 'assertTrue'],
            ["This is a \r\ntest", 'assertFalse'],
            ["This is a \r\n\n test", 'assertFalse'],
            ["This is a\n\n test", 'assertFalse'],
            ["This is a\r\r test", 'assertFalse'],
            ["This is a \r\r\n test", 'assertFalse'],
            ["This is a \r\n\r\ntest", 'assertFalse'],
            ["This is a \r\n\n\r\n test", 'assertFalse'],
            ["This is a \xFF test", 'assertFalse'],
            ["This is a \x7F test", 'assertFalse'],
            ["This is a \x7E test", 'assertTrue'],
        ];
    }

    /**
     * @dataProvider validateValues
     */
    public function testValidatesValuesPerRfc7230(string $value, string $assertion): void
    {
        $this->{$assertion}(HeaderSecurity::isValid($value));
    }

    /** @return array<int, array{0: string}> */
    public function assertValues(): array
    {
        return [
            ["This is a\n test"],
            ["This is a\r test"],
            ["This is a\n\r test"],
            ["This is a \r\ntest"],
            ["This is a \r\n\n test"],
            ["This is a\n\n test"],
            ["This is a\r\r test"],
            ["This is a \r\r\n test"],
            ["This is a \r\n\r\ntest"],
            ["This is a \r\n\n\r\n test"],
        ];
    }

    /**
     * @dataProvider assertValues
     */
    public function testAssertValidRaisesExceptionForInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderSecurity::assertValid($value);
    }
}
