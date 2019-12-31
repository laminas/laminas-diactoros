<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\marshalProtocolVersionFromSapi as laminas_marshalProtocolVersionFromSapi;
use function preg_match;

/**
 * @deprecated Use Laminas\Diactoros\marshalProtocolVersionFromSapi instead
 */
function marshalProtocolVersionFromSapi(array $server) : string
{
    laminas_marshalProtocolVersionFromSapi(...func_get_args());
}
