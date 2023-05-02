# Migration to Version 3

## Removed

The following features were removed for version 3.

### GdImage support in `Stream`

`Laminas\Diactoros\Stream` "supported" usage of resources created via the GD extension.
However, this support was unstable, and largely did not work.
With the update in PHP 8.0 to usage of opaque resource types for all GD resources, it did not work at all.
As such, we have removed the feature entirely.

If you need to stream an image, the recommendation is to use the functionality in the GD extension to write the image to a temporary file (e.g., `php://temp`), and then to pass that to `Laminas\Diactoros\Stream`.
