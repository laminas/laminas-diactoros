<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diactoros;

use UnexpectedValueException;

use function preg_match;

/**
 * Return HTTP protocol version (X.Y) as discovered within a `$_SERVER` array.
 *
 * @param array $server
 * @return string
 * @throws UnexpectedValueException if the $server['SERVER_PROTOCOL'] value is
 *     malformed.
 */
function marshalProtocolVersionFromSapi(array $server)
{
    if (! isset($server['SERVER_PROTOCOL'])) {
        return '1.1';
    }

    if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
        throw new UnexpectedValueException(sprintf(
            'Unrecognized protocol version (%s)',
            $server['SERVER_PROTOCOL']
        ));
    }

    return $matches['version'];
}
