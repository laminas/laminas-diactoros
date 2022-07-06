<?php

declare(strict_types=1);

namespace Zend\Diactoros;

use function func_get_args;
use function Laminas\Diactoros\parseCookieHeader as laminas_parseCookieHeader;

/**
 * @deprecated Use \Laminas\Diactoros\parseCookieHeader instead
 *
 * @param string $cookieHeader A string cookie header value.
 */
function parseCookieHeader($cookieHeader): array
{
    return laminas_parseCookieHeader(...func_get_args());
}
