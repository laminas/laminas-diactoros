<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Laminas\Diactoros\Uri;
use Override;

final class UriTest extends UriIntegrationTest
{
    /** {@inheritDoc} */
    #[Override]
    public function createUri($uri): Uri
    {
        return new Uri($uri);
    }
}
