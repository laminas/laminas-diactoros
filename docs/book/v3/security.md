# Security Features

## ServerRequestFilterInterface defaults

`Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface` is used by `ServerRequestFactory::fromGlobals()` to allow modifying the generated `ServerRequest` instance prior to returning it.
The primary use case is to allow modifying the generated URI based on the presence of headers such as `X-Forwarded-Host`.
When operating behind a reverse proxy, the `Host` header is often rewritten to the name of the node to which the request is being forwarded, and an `X-Forwarded-Host` header is generated with the original `Host` value to allow the server to determine the original host the request was intended for.
We also similarly examine the `X-Forwarded-Port` header.

To accommodate this use case, we provide `Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders`.

Due to potential security issues, it is generally best to only accept these headers if you trust the reverse proxy that has initiated the request.
(This value is found in `$_SERVER['REMOTE_ADDR']`, which is present as `$request->getServerParams()['REMOTE_ADDR']` within PSR-7 implementations.)
`FilterUsingXForwardedHeaders` provides named constructors to allow you to trust these headers from any source (which has been the default behavior of Diactoros since the beginning), or to specify specific IP addresses or CIDR subnets to trust, along with which headers are trusted.
We use this filter by default, marked to trust **only proxies on private subnets**.

If you **do not** need the functionality, we recommend specifying `Laminas\Diactoros\ServerRequestFilter\DoNotFilter` as the configured `FilterServerRequestInterface` in your application.

## Filtering of integer header names

[PSR-7](https://www.php-fig.org/psr/psr-7/) targets [RFC 7230](https://www.rfc-editor.org/rfc/rfc7230).
RFC-7230 defines an ABNF pattern for header field names that allows the possibility of using an integer as a header field; e.g.,

```http
1234: header value
```

The PSR-7, `Psr\Http\MessageInterface::getHeaders()` method requires implementations to return an associative array, where the key is the header field name.
This triggers an interesting quirk in PHP: when adding a value to an array using a string that consists of an integer value, PHP will convert this value to an integer (see [PHP bug 80309](https://bugs.php.net/bug.php?id=80309) for more details).
This presents several issues:

- First, it means that consumers cannot depend on the header field name returned being a string.
- Second, our own validation of header field name will fail, as it will not see a string.

Normally, this will not present an issue, as the way to add headers to a message is via the `MessageInterface::withHeader()` and `MessageInterface::withAddedHeader()` methods, which both require a `string` name argument.
However, when using `Laminas\Diactoros\ServerRequestFactory::fromGlobals()`, it can present a problem if any discovered headers have field names that evaluate to integers.

To prevent issues, as of version 3.0.0, the `ServerRequestFactory` implementation in Diactoros filters out any headers that evaluate to integers.
If you wish to accept these anyways, we strongly recommend that you modify your web server to rewrite the incoming header field name to add a prefix or suffix string (e.g., `X-Digit-1`, `1-Digit`).
