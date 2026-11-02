<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\TestAsset;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- stream wrapper method names are fixed by PHP

/**
 * Stream wrapper whose directories are writable but whose files accept no bytes,
 * simulating ENOSPC / EIO / quota exhaustion on the move target.
 *
 * The methods are called by PHP's stream layer, not by userland code.
 *
 * @psalm-suppress MissingConstructor
 */
final class FailingWriteStreamWrapper
{
    public const SCHEME = 'diactoros-failwrite';

    /**
     * @var resource|null
     * @psalm-suppress PossiblyUnusedProperty
     */
    public $context;

    /** @psalm-suppress PossiblyUnusedMethod, UnusedParam */
    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    /** @psalm-suppress PossiblyUnusedMethod, UnusedParam */
    public function stream_write(string $data): int
    {
        return 0;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function stream_flush(): bool
    {
        return false;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function stream_close(): void
    {
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function stream_eof(): bool
    {
        return true;
    }

    /**
     * @return array<string, int>
     * @psalm-suppress PossiblyUnusedMethod, UnusedParam
     */
    public function url_stat(string $path, int $flags): array
    {
        return [
            'dev'   => 0,
            'ino'   => 0,
            'mode'  => 0040777,
            'nlink' => 1,
            'uid'   => 0,
            'gid'   => 0,
            'rdev'  => 0,
            'size'  => 0,
        ];
    }
}
