<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Diactoros;

use function Laminas\Diactoros\parseCookieHeader as laminas_parseCookieHeader;

/**
 * @deprecated Use Laminas\Diactoros\parseCookieHeader instead
 */
function parseCookieHeader($cookieHeader)
{
    return laminas_parseCookieHeader(...func_get_args());
}
