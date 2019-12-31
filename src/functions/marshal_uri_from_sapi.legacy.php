<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function array_change_key_case;
use function array_key_exists;
use function explode;
use function implode;
use function is_array;
use function Laminas\Diactoros\marshalUriFromSapi as laminas_marshalUriFromSapi;
use function ltrim;
use function preg_match;
use function preg_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;

/**
 * @deprecated Use Laminas\Diactoros\marshalUriFromSapi instead
 */
function marshalUriFromSapi(array $server, array $headers) : Uri
{
    laminas_marshalUriFromSapi(...func_get_args());
}
