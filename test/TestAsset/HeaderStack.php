<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\TestAsset;

/**
 * Store output artifacts
 */
class HeaderStack
{
    /** @var string[][] */
    private static array $data = [];

    /**
     * Reset state
     */
    public static function reset(): void
    {
        self::$data = [];
    }

    /**
     * Push a header on the stack
     *
     * @param string[] $header
     */
    public static function push(array $header): void
    {
        self::$data[] = $header;
    }

    /**
     * Return the current header stack
     *
     * @return string[][]
     */
    public static function stack(): array
    {
        return self::$data;
    }

    /**
     * Verify if there's a header line on the stack
     *
     * @param string $header
     */
    public static function has($header): bool
    {
        foreach (self::$data as $item) {
            if ($item['header'] === $header) {
                return true;
            }
        }

        return false;
    }
}
