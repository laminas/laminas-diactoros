<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Diactoros\TestAsset;

/**
 * Store output artifacts
 */
class HeaderStack
{
    /**
     * @var string[][]
     */
    private static $data = [];

    /**
     * Reset state
     */
    public static function reset()
    {
        self::$data = [];
    }

    /**
     * Push a header on the stack
     *
     * @param string[] $header
     */
    public static function push(array $header)
    {
        self::$data[] = $header;
    }

    /**
     * Return the current header stack
     *
     * @return string[][]
     */
    public static function stack()
    {
        return self::$data;
    }

    /**
     * Verify if there's a header line on the stack
     *
     * @param string $header
     *
     * @return bool
     */
    public static function has($header)
    {
        foreach (self::$data as $item) {
            if ($item['header'] === $header) {
                return true;
            }
        }

        return false;
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
 * @param string   $string
 * @param bool     $replace
 * @param int|null $statusCode
 */
function header($string, $replace = true, $statusCode = null)
{
    HeaderStack::push(
        [
            'header'      => $string,
            'replace'     => $replace,
            'status_code' => $statusCode,
        ]
    );
}
