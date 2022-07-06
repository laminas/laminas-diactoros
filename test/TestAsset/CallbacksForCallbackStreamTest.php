<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros\TestAsset;

class CallbacksForCallbackStreamTest
{
    /**
     * Sample callback to use with testing.
     */
    public function sampleCallback(): string
    {
        return __METHOD__;
    }

    /**
     * Sample static callback to use with testing.
     */
    public static function sampleStaticCallback(): string
    {
        return __METHOD__;
    }
}
