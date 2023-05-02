# Preparing for Version 3

## ServerRequestFilterInterface defaults

Introduced in version 2.11.1, the `Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface` is used by `ServerRequestFactory::fromGlobals()` to allow modifying the generated `ServerRequest` instance prior to returning it.
The primary use case is to allow modifying the generated URI based on the presence of headers such as `X-Forwarded-Host`.
When operating behind a reverse proxy, the `Host` header is often rewritten to the name of the node to which the request is being forwarded, and an `X-Forwarded-Host` header is generated with the original `Host` value to allow the server to determine the original host the request was intended for.
(We have always examined the `X-Forwarded-Proto` header; as of 2.11.1, we also examine the `X-Forwarded-Port` header.)

To accommodate this use case, we created `Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders`.

Due to potential security issues, it is generally best to only accept these headers if you trust the reverse proxy that has initiated the request.
(This value is found in `$_SERVER['REMOTE_ADDR']`, which is present as `$request->getServerParams()['REMOTE_ADDR']` within PSR-7 implementations.)
`FilterUsingXForwardedHeaders` provides named constructors to allow you to trust these headers from any source (which has been the default behavior of Diactoros since the beginning), or to specify specific IP addresses or CIDR subnets to trust, along with which headers are trusted.
To prevent backwards compatibility breaks, we use this filter by default, marked to trust **only proxies on private subnets**.

Features will be added to the 3.11.0 version of [mezzio/mezzio](https://github.com/mezzio/mezzio) that will allow configuring the `Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface` instance, and we recommend explicitly configuring this to utilize the `FilterUsingXForwardedHeaders` if you depend on this functionality.
If you **do not** need the functionality, we recommend specifying `Laminas\Diactoros\ServerRequestFilter\DoNotFilter` as the configured `FilterServerRequestInterface` in your application immediately.

We will update this documentation with a link to the related functionality in mezzio/mezzio when it is published.
