<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\parseCookieHeader as laminas_parseCookieHeader;
use function preg_match_all;
use function urldecode;

/**
 * @deprecated Use Laminas\Diactoros\parseCookieHeader instead
 */
function parseCookieHeader($cookieHeader) : array
{
    laminas_parseCookieHeader(...func_get_args());
}
