<?php

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;

class InvalidForwardedHeaderNameException extends RuntimeException implements ExceptionInterface
{
    /** @param mixed $name */
    public static function forHeader($name): self
    {
        if (! is_string($name)) {
            $name = sprintf('(value of type %s)', is_object($name) ? get_class($name) : gettype($name));
        }

        return new self(sprintf(
            'Invalid X-Forwarded-* header name "%s" provided to %s',
            $name,
            FilterUsingXForwardedHeaders::class
        ));
    }
}
