# Migration to Version 3

## Changed

The following features were changed in version 3.

### PSR-7 v2 support

This version adds support for PSR-7 version 2.

Diactoros 2.25.0 added support for PSR-7 version 1.1, and at that time, already had return type declarations that would mostly fulfill PSR-7 version 2.
However, there were two locations in PSR-7 where `void` returns were added:

- `StreamInterface::seek()`
- `UploadedFileInterface::moveTo()`

Since absence of a return type declaration implies `mixed` in PHP, switching to `void` is always considered a BC break, as it reduces the allowed behavior.
As such, any consumers _extending_ our `Stream` or `UploadedFile` classes and overriding either of these methods would experience a BC breaking change due to types.

For consumers, usage should be completely backwards compatible, however.

### ServerRequestFactory::fromGlobals

The factory `Laminas\Diactoros\ServerRequestFactory::fromGlobals()` was modified such that passing empty array values for arguments that accept `null` or an array now will not use the associated superglobal in that scenario.
Previously, an empty array value was treated as identical to `null`, and would cause the factory to fallback to superglobals; now, this is a way to provide an empty set for the associated value(s).

## Removed

The following features were removed for version 3.

### GdImage support in `Stream`

`Laminas\Diactoros\Stream` "supported" usage of resources created via the GD extension.
However, this support was unstable, and largely did not work.
With the update in PHP 8.0 to usage of opaque resource types for all GD resources, it did not work at all.
As such, we have removed the feature entirely.

If you need to stream an image, the recommendation is to use the functionality in the GD extension to write the image to a temporary file (e.g., `php://temp`), and then to pass that to `Laminas\Diactoros\Stream`.

### marshalUriFromSapi function

The `Laminas\Diactoros\marshalUriFromSapi()` function was deprecated starting in version 2.11.0, and now removed.
The functionality that was present in it was moved to `Laminas\Diactoros\UriFactory::createFromSapi()`.
If you were using the function previously, use this static method instead.

### PhpInputStream

The class `Laminas\Diactoros\PhpInputStream` was originally developed prior to PHP 5.6, when `php://input` was _read-once_.
As such, we needed to handle it specially to ensure it could be read multiple times.

Since 5.6 and onwards, the stream is seekable and can be re-used.

With version 3, we have removed it, and modified our `ServerRequest` such that it now uses a read-only `Stream` referencing `php://input` as its stream resource.
If you were using the class directly, you can instead use `new Laminas\Diactoros\Stream('php://input', 'r')` to achieve the same result.
