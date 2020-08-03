<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Diactoros;

trait StreamTrait
{
    /**
     * Determine if a resource is one of the resource types allowed to instantiate a Stream
     *
     * @param resource $resource Stream resource.
     * @return bool
     */
    protected function isValidStreamResourceType($resource)
    {
        return (is_resource($resource) && in_array(get_resource_type($resource), Stream::ALLOWED_TYPES));
    }
}
