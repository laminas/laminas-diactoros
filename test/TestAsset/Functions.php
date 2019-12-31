<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diactoros;

/**
 * Store output artifacts
 */
class HeaderStack
{
    /**
     * @var array
     */
    private static $data = array();

    /**
     * Reset state
     */
    public static function reset()
    {
        self::$data = array();
    }

    /**
     * Push a header on the stack
     *
     * @param string $header
     */
    public static function push($header)
    {
        self::$data[] = $header;
    }

    /**
     * Return the current header stack
     *
     * @return array
     */
    public static function stack()
    {
        return self::$data;
    }
}

/**
 * Have headers been sent?
 *
 * @return false
 */
function headers_sent()
{
    return false;
}

/**
 * Emit a header, without creating actual output artifacts
 *
 * @param string $value
 */
function header($value)
{
    HeaderStack::push($value);
}
