<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    // Set paths
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ]);

    // Define set list to upgrade PHP
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);
};
