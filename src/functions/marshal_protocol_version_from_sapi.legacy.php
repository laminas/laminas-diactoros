<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Diactoros;

use function Laminas\Diactoros\marshalProtocolVersionFromSapi as laminas_marshalProtocolVersionFromSapi;

/**
 * @deprecated Use Laminas\Diactoros\marshalProtocolVersionFromSapi instead
 */
function marshalProtocolVersionFromSapi(array $server)
{
    return laminas_marshalProtocolVersionFromSapi(...func_get_args());
}
