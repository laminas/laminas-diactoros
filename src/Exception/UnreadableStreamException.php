<?php

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use RuntimeException;

class UnreadableStreamException extends RuntimeException implements ExceptionInterface
{
    public static function dueToConfiguration() : self
    {
        return new self('Stream is not readable');
    }

    public static function dueToMissingResource() : self
    {
        return new self('No resource available; cannot read');
    }

    public static function dueToPhpError() : self
    {
        return new self('Error reading stream');
    }

    public static function forCallbackStream() : self
    {
        return new self('Callback streams cannot read');
    }
}
