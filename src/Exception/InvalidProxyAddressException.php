<?php

declare(strict_types=1);

namespace Laminas\Diactoros\Exception;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class InvalidProxyAddressException extends RuntimeException implements ExceptionInterface
{
    /** @param mixed $proxy */
    public static function forInvalidProxyArgument($proxy): self
    {
        $type = is_object($proxy) ? get_class($proxy) : gettype($proxy);
        return new self(sprintf(
            'Invalid proxy of type "%s" provided;'
            . ' must be a valid IPv4 or IPv6 address, optionally with a subnet mask provided'
            . ' or an array of such values',
            $type,
        ));
    }

    public static function forAddress(string $address): self
    {
        return new self(sprintf(
            'Invalid proxy address "%s" provided;'
            . ' must be a valid IPv4 or IPv6 address, optionally with a subnet mask provided',
            $address,
        ));
    }
}
