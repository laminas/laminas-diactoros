<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use GdImage;

use function sprintf;

/**
 * This class purposely does not override the $resource property in order to
 * allow it to extend Stream. If defined here with a type, PHP will raise a
 * fatal error complaining that it must not define a type for the property.
 */
class ImageStream extends Stream
{
    /**
     * This purposely does not explicitly override the $resource property in
     * order to allow it to extend Stream. If defined here with a type, PHP will
     * raise a fatal error complaining that it must not define a type for the
     * property.
     *
     * @var null|GdImage
     */
    protected $resource;

    public function __construct(
        GdImage $resource,
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|GdImage
     */
    public function detach(): ?GdImage
    {
        $resource       = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Attach a new stream/resource to the instance.
     *
     * @param GdImage $resource
     * @param string $mode Unused
     * @throws Exception\InvalidArgumentException When provided a non-GdImage resource.
     */
    public function attach($resource, string $mode = 'r'): void
    {
        if (! $resource instanceof GdImage) {
            throw new Exception\InvalidArgumentException(sprintf(
                'When attaching a resource to %s, resource must be a GdImage',
                $this::class,
            ));
        }

        $this->resource = $resource;
    }
}
