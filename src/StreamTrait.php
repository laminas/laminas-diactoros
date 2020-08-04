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
     * A list of types that are allowed to instantiate a Stream
     */
    private $allowedTypes = ['gd', 'stream'];

    /**
     * Determine if a resource is one of the resource types allowed to instantiate a Stream
     *
     * @param resource $resource Stream resource.
     * @return bool
     */
    private function isValidStreamResourceType($resource)
    {
        return (is_resource($resource) && in_array(get_resource_type($resource), $this->allowedTypes, true));
    }
}
