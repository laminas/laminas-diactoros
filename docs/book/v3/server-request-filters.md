# Server Request Filters

INFO: **New Feature**
Available since version 2.11.1

Server request filters allow you to modify the initial state of a generated `ServerRequest` instance as returned from `Laminas\Diactoros\ServerRequestFactory::fromGlobals()`.
Common use cases include:

- Generating and injecting a request ID.
- Modifying the request URI based on headers provided (e.g., based on the `X-Forwarded-Host` or `X-Forwarded-Proto` headers).

## FilterServerRequestInterface

A request filter implements `Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface`:

```php
namespace Laminas\Diactoros\ServerRequestFilter;

use Psr\Http\Message\ServerRequestInterface;

interface FilterServerRequestInterface
{
    public function __invoke(ServerRequestInterface $request): ServerRequestInterface;
}
```

## Implementations

We provide the following implementations:

- `DoNotFilter`: returns the provided `$request` verbatim.
- `FilterUsingXForwardedHeaders`: if the originating request comes from a trusted proxy, examines the `X-Forwarded-*` headers, and returns the request instance with a URI instance that reflects those headers.

### DoNotFilter

This filter returns the `$request` argument back verbatim when invoked.

### FilterUsingXForwardedHeaders

Servers behind a reverse proxy need mechanisms to determine the original URL requested.
As such, reverse proxies have provided a number of mechanisms for delivering this information, with the use of `X-Forwarded-*` headers being the most prevalant.
These include:

- `X-Forwarded-Host`: the original `Host` header value.
- `X-Forwarded-Port`: the original port included in the `Host` header value.
- `X-Forwarded-Proto`: the original URI scheme used to make the request (e.g., "http" or "https").

`Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders` provides named constructors for choosing whether to never trust proxies, always trust proxies, or choose wich proxies and/or headers to trust in order to modify the URI composed in the request instance to match the original request.
These named constructors are:

- `FilterUsingXForwardedHeadersFactory::trustProxies(string[] $proxyCIDRList, string[] $trustedHeaders = FilterUsingXForwardedHeaders::X_FORWARDED_HEADERS): void`: when this method is called, only requests originating from the trusted proxy/ies will be considered, as well as only the headers specified.
  Proxies may be specified by IP address, or using [CIDR notation](https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing) for subnets; both IPv4 and IPv6 are accepted.
  The special string "*" will be translated to two entries, `0.0.0.0/0` and `::/0`.
- `FilterUsingXForwardedHeaders::trustAny(): void`: when this method is called, the filter will trust requests from any origin, and use any of the above headers to modify the URI instance.
  It is functionally equivalent to `FilterUsingXForwardedHeaders::trustProxies(['*'])`.
- `FilterUsingXForwardedHeaders::trustReservedSubnets(): void`: when this method is called, the filter will trust requests made from reserved, private subnets.
  It is functionally equivalent to `FilterUsingXForwardedHeaders::trustProxies()` with the following elements in the `$proxyCIDRList`:
  - 10.0.0.0/8
  - 127.0.0.0/8
  - 172.16.0.0/12
  - 192.168.0.0/16
  - ::1/128 (IPv6 localhost)
  - fc00::/7 (IPv6 private networks)
  - fe80::/10 (IPv6 local-link addresses)

Internally, the filter checks the `REMOTE_ADDR` server parameter (as retrieved from `getServerParams()`) and compares it against each proxy listed; the first to match indicates trust.

#### Constants

The `FilterUsingXForwardedHeaders` defines the following constants for use in specifying various headers:

- `HEADER_HOST`: corresponds to `X-Forwarded-Host`.
- `HEADER_PORT`: corresponds to `X-Forwarded-Port`.
- `HEADER_PROTO`: corresponds to `X-Forwarded-Proto`.

#### Example usage

Trusting all `X-Forwarded-*` headers from any source:

```php
$filter = FilterUsingXForwardedHeaders::trustAny();
```

Trusting only the `X-Forwarded-Host` header from any source:

```php
$filter = FilterUsingXForwardedHeaders::trustProxies('0.0.0.0/0', [FilterUsingXForwardedHeaders::HEADER_HOST]);
```

Trusting the `X-Forwarded-Host` and `X-Forwarded-Proto` headers from a single Class C subnet:

```php
$filter = FilterUsingXForwardedHeaders::trustProxies(
    '192.168.1.0/24',
    [FilterUsingXForwardedHeaders::HEADER_HOST, FilterUsingXForwardedHeaders::HEADER_PROTO]
);
```

Trusting the `X-Forwarded-Host` header from either a Class A or a Class C subnet:

```php
$filter = FilterUsingXForwardedHeaders::trustProxies(
    ['10.1.1.0/16', '192.168.1.0/24'],
    [FilterUsingXForwardedHeaders::HEADER_HOST, FilterUsingXForwardedHeaders::HEADER_PROTO]
);
```

Trusting any `X-Forwarded-*` header from any private subnet:

```php
$filter = FilterUsingXForwardedHeaders::trustReservedSubnets();
```
