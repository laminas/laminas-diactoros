<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use InvalidArgumentException;
use Laminas\Diactoros\HeaderSecurity;
use PHPUnit\Framework\TestCase;

final class HeaderSecurityTest extends TestCase
{
    /**
     * Data for filter value
     *
     * @return non-empty-list<array{non-empty-string, non-empty-string}>
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
            ["This is a test\n", "This is a test"],
        ];
    }

    /**
     * @dataProvider getFilterValues
     * @group ZF2015-04
     * @param non-empty-string $value
     * @param non-empty-string $expected
     */
    public function testFiltersValuesPerRfc7230(string $value, string $expected): void
    {
        $this->assertSame($expected, HeaderSecurity::filter($value));
    }

    /** @return non-empty-list<array{non-empty-string, bool}> */
    public function validateValues(): array
    {
        return [
            ["This is a\n test", false],
            ["This is a\r test", false],
            ["This is a\n\r test", false],
            ["This is a\r\n  test", true],
            ["This is a \r\ntest", false],
            ["This is a \r\n\n test", false],
            ["This is a\n\n test", false],
            ["This is a\r\r test", false],
            ["This is a \r\r\n test", false],
            ["This is a \r\n\r\ntest", false],
            ["This is a \r\n\n\r\n test", false],
            ["This is a \xFF test", false],
            ["This is a \x7F test", false],
            ["This is a \x7E test", true],
            ["This is a test\n", false],
        ];
    }

    /**
     * @dataProvider validateValues
     * @group ZF2015-04
     * @param non-empty-string $value
     */
    public function testValidatesValuesPerRfc7230(string $value, bool $expected): void
    {
        self::assertSame($expected, HeaderSecurity::isValid($value));
    }

    /** @return non-empty-list<array{non-empty-string}> */
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
            ["This is a test\n"],
        ];
    }

    /**
     * @dataProvider assertValues
     * @group ZF2015-04
     * @param non-empty-string $value
     */
    public function testAssertValidRaisesExceptionForInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderSecurity::assertValid($value);
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function assertNames(): array
    {
        return [
            ["test\n"],
            ["\ntest"],
            ["foo\r\n bar"],
            ["f\x00o"],
            ["foo bar"],
            [":foo"],
            ["foo:"],
        ];
    }

    /**
     * @dataProvider assertNames
     * @param non-empty-string $value
     */
    public function testAssertValidNameRaisesExceptionForInvalidName(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderSecurity::assertValidName($value);
    }

    /** @psalm-return non-empty-array<non-empty-string, array{0: int}> */
    public function provideValidNumericHeaderNameValues(): array
    {
        return [
            'negative' => [-1],
            'zero'     => [0],
            'int'      => [1],
        ];
    }

    /** @dataProvider provideValidNumericHeaderNameValues */
    public function testAssertValidNameDoesNotRaiseExceptionForValidNumericValues(int $value): void
    {
        $this->assertNull(HeaderSecurity::assertValidName($value));
    }
}
