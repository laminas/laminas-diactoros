<?php

declare(strict_types=1);

namespace Laminas\Diactoros\Response;

use function array_keys;
use function array_reduce;
use function strtolower;

trait InjectContentTypeTrait
{
    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param array<non-empty-string, string|string[]> $headers
     * @return array<non-empty-string, string|string[]> Headers with injected Content-Type
     */
    private function injectContentType(string $contentType, array $headers): array
    {
        $hasContentType = array_reduce(
            array_keys($headers),
            static fn(bool $carry, string $item): bool => $carry ?: strtolower($item) === 'content-type',
            false
        );

        if (! $hasContentType) {
            $headers['content-type'] = [$contentType];
        }

        return $headers;
    }
}
