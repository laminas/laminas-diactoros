<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Diactoros;

use function Laminas\Diactoros\normalizeUploadedFiles as laminas_normalizeUploadedFiles;

/**
 * @deprecated Use Laminas\Diactoros\normalizeUploadedFiles instead
 */
function normalizeUploadedFiles(array $files)
{
    return laminas_normalizeUploadedFiles(...func_get_args());
}
