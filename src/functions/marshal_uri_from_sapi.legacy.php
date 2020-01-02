<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Diactoros;

use function Laminas\Diactoros\marshalUriFromSapi as laminas_marshalUriFromSapi;

/**
 * @deprecated Use Laminas\Diactoros\marshalUriFromSapi instead
 */
function marshalUriFromSapi(array $server, array $headers)
{
    return laminas_marshalUriFromSapi(...func_get_args());
}
