<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diactoros\Exception;

use BadMethodCallException;

/**
 * Exception indicating a deprecated method.
 */
class DeprecatedMethodException extends BadMethodCallException implements ExceptionInterface
{
}
