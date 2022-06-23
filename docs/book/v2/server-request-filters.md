# Server Request Filters

INFO: **New Feature**
Available since version 2.11.1

Server request filters allow you to modify the initial state of a generated `ServerRequest` instance as returned from `Laminas\Diactoros\ServerRequestFactory::fromGlobals()`.
Common use cases include:

- Generating and injecting a request ID.
- Modifying the request URI based on headers provided (e.g., based on the `X-Forwarded-Host` or `X-Forwarded-Proto` headers).

## ServerRequestFilterInterface

A request filter implements `Laminas\Diactoros\ServerRequestFilter\ServerRequestFilterInterface`:

```php
namespace Laminas\Diactoros\ServerRequestFilter;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFilterInterface
{
    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface;
}
```

## Implementations

We provide the following implementations:

- `NoOpRequestFilter`: returns the provided `$request` verbatim.
- `XForwardedHeaderFilter`: if the originating request comes from a trusted proxy, examines the `X-Forwarded-*` headers, and returns the request instance with a URI instance that reflects those headers.

### NoOpRequestFilter

This filter returns the `$request` argument back verbatim when invoked.

#### NoOpRequestFilterFactory

Diactoros also ships with a factory for generating a `Laminas\Diactoros\ServerRequestFilter\NoOpRequestFilter` via the `Laminas\Diactoros\ServerRequestFilter\NoOpRequestFilterFactory` class.
Register it as follows:

```php
$config = [
    'dependencies' => [
        'factories' => [
            \Laminas\Diactoros\ServerRequestFilter\ServerRequestFilterInterface::class =>
                \Laminas\Diactoros\ServerRequestFilter\NoOpRequestFilterFactory::class,
        ],
    ],
];
```

### XForwardedHeaderFilter

Servers behind a reverse proxy need mechanisms to determine the original URL requested.
As such, reverse proxies have provided a number of mechanisms for delivering this information, with the use of `X-Forwarded-*` headers being the most prevalant.
These include:

- `X-Forwarded-Host`: the original `Host` header value.
- `X-Forwarded-Port`: the original port included in the `Host` header value.
- `X-Forwarded-Proto`: the original URI scheme used to make the request (e.g., "http" or "https").

`Laminas\Diactoros\ServerRequestFilter\XForwardedHeaderFilter` provides named constructors for choosing whether to never trust proxies, always trust proxies, or choose wich proxies and/or headers to trust in order to modify the URI composed in the request instance to match the original request.
These named constructors are:

- `XForwardedHeaderFilter::trustNone(): void`: when this method is called, the filter will not trust any proxies, and return the request back verbatim.
- `XForwardedHeaderFilter::trustAny(): void`: when this method is called, the filter will trust requests from any origin, and use any of the above headers to modify the URI instance.
- `XForwardedHeaderFilterFactory::trustProxies(string|string[] $proxies, string[] $trustedHeaders = XForwardedHeaderFilter::X_FORWARDED_HEADERS): void`: when this method is called, only requests originating from the trusted proxy/ies will be considered, as well as only the headers specified.

When providing one or more proxies to `trustProxies()`, the values may be exact IP addresses, or subnets specified by [CIDR notation](https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing).
Internally, the filter checks the `REMOTE_ADDR` server parameter (as retrieved from `getServerParams()`) and compares it against each proxy listed; the first to match indicates trust.

#### Constants

The `XForwardedHeaderFilter` defines the following constants for use in specifying various headers:

- `HEADER_HOST`: corresponds to `X-Forwarded-Host`.
- `HEADER_PORT`: corresponds to `X-Forwarded-Port`.
- `HEADER_PROTO`: corresponds to `X-Forwarded-Proto`.
- `X_FORWARDED_HEADERS`: corresponds to an array consisting of all of the above constants.

#### Example usage

Trusting all `X-Forwarded-*` headers from any source:

```php
$filter = XForwardedHeaderFilter::trustAny();
```

Trusting only the `X-Forwarded-Host` header from any source:

```php
$filter = XForwardedHeaderFilter::trustProxies('0.0.0.0/0', [XForwardedHeaderFilter::HEADER_HOST]);
```

Trusting the `X-Forwarded-Host` and `X-Forwarded-Proto` headers from a Class C subnet:

```php
$filter = XForwardedHeaderFilter::trustProxies(
    '192.168.1.0/24',
    [XForwardedHeaderFilter::HEADER_HOST, XForwardedHeaderFilter::HEADER_PROTO]
);
```

Trusting the `X-Forwarded-Host` header from either a Class A or a Class C subnet:

```php
$filter = XForwardedHeaderFilter::trustProxies(
    ['10.1.1.0/16', '192.168.1.0/24'],
    [XForwardedHeaderFilter::HEADER_HOST, XForwardedHeaderFilter::HEADER_PROTO]
);
```

#### XForwardedHeaderFilterFactory

Diactoros also ships with a factory for generating a `Laminas\Diactoros\ServerRequestFilter\XForwardedHeaderFilter` via the `Laminas\Diactoros\ServerRequestFilter\XForwardedHeaderFilterFactory` class.
This factory looks for the following configuration in order to generate an instance:

```php
$config = [
    'laminas-diactoros' => [
        'x-forwarded-header-filter' => [
            'trust-any' => bool,
            'trusted-proxies' => string|string[],
            'trusted-headers' => string[],
        ],
    ],
];
```

- The `trust-any` key should be a boolean.
  By default, it is `false`; toggling it `true` will use the `trustAny()` constructor to generate the instance.
  This flag overrides the `trusted-proxies` configuration.
- The `trusted-proxies` array should be a string IP address or CIDR notation, or an array of such values, each indicating a trusted proxy server or subnet of such servers.
- The `trusted-headers` array should consist of one or more of the `X-Forwarded-Host`, `X-Forwarded-Port`, or `X-Forwarded-Proto` header names; the values are case insensitive.
  When the configuration is omitted or the array is empty, the assumption is to honor all `X-Forwarded-*` headers for trusted proxies.

Register the factory using the `Laminas\Diactoros\ServerRequestFilter\ServerRequestFilterInterface` key:

```php
$config = [
    'dependencies' => [
        'factories' => [
            \Laminas\Diactoros\ServerRequestFilter\ServerRequestFilterInterface::class =>
                \Laminas\Diactoros\ServerRequestFilter\XForwardedHeaderFilterFactory::class,
        ],
    ],
];
```
