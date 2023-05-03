# Migration to Version 3

## Changed

The following features were changed in version 3.

### ServerRequestFactory::fromGlobals

The factory `Laminas\Diactoros\ServerRequestFactory::fromGlobals()` was modified such that passing empty array values for arguments that accept `null` or an array now will not use the associated superglobal in that scenario.
Previously, an empty array value was treated as identical to `null`, and would cause the factory to fallback to superglobals; now, this is a way to provide an empty set for the associated value(s).

### HeaderSecurity::assertValidName behavior

Since it was introduced, `Laminas\Diactoros\HeaderSecurity::assertValidName()`, used in both request and response classes to validate header names, has considered integer headers invalid.
However [RFC 7230 Section 3.2.6](https://www.rfc-editor.org/rfc/rfc7230#section-3.2.6) illustrates that numeric values are allowed as header names.
Unfortunately, the behavior of the PHP engine itself is such that string keys that contain integer values are cast to an integer, which would then cause this method to flag a header as invalid.

Version 3 loosens the logic slightly, adding a check for a non-string, non-integer value first.
If the value is an integer, it is cast to a string, and then checked against a regular expression that matches the RFC 7230 ABNF for valid header values.
This allows integer values to be used as header names.

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
