# Request Filters

> - Since laminas/laminas-diactoros 2.11.1

Request filters allow you to modify the initial state of a generated `ServerRequest` instance as returned from `Laminas\Diactoros\ServerRequestFactory::fromGlobals()`.
Common use cases include:

- Generating and injecting a request ID.
- Modifying the request URI based on headers provided (e.g., based on the `X-Forwarded-Host` or `X-Forwarded-Proto` headers).

## RequestFilterInterface

A request filter implements `Laminas\Diactoros\RequestFilter\RequestFilterInterface`:

```php
namespace Laminas\Diactoros\RequestFilter;

use Psr\Http\Message\ServerRequestInterface;

interface RequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface;
}
```

## Implementations

We provide the following implementations:

- `NoOpRequestFilter`: returns the provided `$request` verbatim.
- `LegacyXForwardedHeaderFilter`: if the originating request comes from a trusted proxy, examines the `X-Forwarded-*` headers, and returns the request instance with a URI instanct that reflects those headers.

### LegacyXForwardedHeaderFilter

Servers behind a reverse proxy need mechanisms to determine the original URL requested.
As such, reverse proxies have provided a number of mechanisms for delivering this information, with the use of `X-Forwarded-*` headers being the most prevalant.
These include:

- `X-Forwarded-Host`: the original `Host` header value.
- `X-Forwarded-Port`: the original port included in the `Host` header value.
- `X-Forwarded-Proto`: the original URI scheme used to make the request (e.g., "http" or "https").

`Laminas\Diactoros\RequestFilter\LegacyXForwardedHeaderFilter` provides mechanisms for accepting these headers and using them to modify the URI composed in the request instance to match the original request.
These methods are:

- `trustAny(): void`: when this method is called, the filter will trust requests from any origin, and use any of the above headers to modify the URI instance.
- `trustProxies(string|string[] $proxies, string[] $trustedHeaders = LegacyXForwardedHeaderFilter::X_FORWARDED_HEADERS): void`: when this method is called, only requests originating from the trusted proxy/ies will be considered, as well as only the headers specified.

Order of operations matters when configuring the instance.
If `trustAny()` is called after `trustProxies()`, the filter will trust any request.
If `trustProxies()` is called after `trustAny()`, the filter will trust only the proxy/ies provided to `trustProxies()`.

When providing one or more proxies to `trustProxies()`, the values may be exact IP addresses, or subnets specified by [CIDR notation](https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing).
Internally, the filter checks the `REMOTE_ADDR` server parameter (as retrieved from `getServerParams()`) and compares it against each proxy listed; the first to match indicates trust.

#### Constants

The `LegacyXForwardedHeaderFilter` defines the following constants for use in specifying various headers:

- `HEADER_HOST`: corresponds to `X-Forwarded-Host`.
- `HEADER_PORT`: corresponds to `X-Forwarded-Port`.
- `HEADER_PROTO`: corresponds to `X-Forwarded-Proto`.
- `X_FORWARDED_HEADERS`: corresponds to an array consisting of all of the above costants.

#### Example usage

Trusting all `X-Forwarded-*` headers from any source:

```php
$filter = new LegacyXForwardedHeaderFilter();
$filter->trustAny();
```

Trusting only the `X-Forwarded-Host` header from any source:

```php
$filter = new LegacyXForwardedHeaderFilter();
$filter->trustProxies('0.0.0.0/0', [LegacyXForwardedHeaderFilter::HEADER_HOST]);
```

Trusting the `X-Forwarded-Host` and `X-Forwarded-Proto` headers from a Class C subnet:

```php
$filter = new LegacyXForwardedHeaderFilter();
$filter->trustProxies(
    '192.168.1.0/24',
    [LegacyXForwardedHeaderFilter::HEADER_HOST, LegacyXForwardedHeaderFilter::HEADER_PROTO]
);
```

Trusting the `X-Forwarded-Host` header from either a Class A or a Class C subnet:

```php
$filter = new LegacyXForwardedHeaderFilter();
$filter->trustProxies(
    ['10.1.1.0/16', '192.168.1.0/24'],
    [LegacyXForwardedHeaderFilter::HEADER_HOST, LegacyXForwardedHeaderFilter::HEADER_PROTO]
);
```
