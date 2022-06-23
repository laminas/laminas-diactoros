<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\Exception\InvalidForwardedHeaderNameException;
use Laminas\Diactoros\Exception\InvalidProxyAddressException;
use Psr\Http\Message\ServerRequestInterface;

final class XForwardedRequestFilter implements ServerRequestFilterInterface
{
    public const HEADER_HOST  = 'X-FORWARDED-HOST';
    public const HEADER_PORT  = 'X-FORWARDED-PORT';
    public const HEADER_PROTO = 'X-FORWARDED-PROTO';

    public const X_FORWARDED_HEADERS = [
        self::HEADER_HOST,
        self::HEADER_PORT,
        self::HEADER_PROTO,
    ];

    /**
     * @todo Toggle this to false for version 3.0.
     * @var bool
     */
    private $trustAny = true;

    /**
     * @var string[]
     * @psalm-var array<array-key, XForwardedRequestFilter::HEADER_*>
     */
    private $trustedHeaders = [];

    /** @var string[] */
    private $trustedProxies = [];

    /**
     * Do not trust any proxies, nor any X-FORWARDED-* headers.
     */
    public static function trustNone(): self
    {
        $filter = new self();
        $filter->trustAny = false;

        return $filter;
    }

    /**
     * Trust any X-FORWARDED-* headers from any address.
     *
     * WARNING: Only do this if you know for certain that your application
     * sits behind a trusted proxy that cannot be spoofed. This should only
     * be the case if your server is not publicly addressable, and all requests
     * are routed via a reverse proxy (e.g., a load balancer, a server such as
     * Caddy, when using Traefik, etc.).
     */
    public static function trustAny(): self
    {
        $filter = new self();
        $filter->trustAny       = true;
        $filter->trustedHeaders = self::X_FORWARDED_HEADERS;

        return $filter;
    }

    /**
     * @param string|string[] $proxies
     * @param array<int, self::HEADER_*> $trustedHeaders
     * @throws InvalidProxyAddressException
     * @throws InvalidForwardedHeaderNameException
     */
    public static function trustProxies(
        $proxies,
        array $trustedHeaders = self::X_FORWARDED_HEADERS
    ): self {
        $proxies = self::normalizeProxiesList($proxies);
        self::validateTrustedHeaders($trustedHeaders);

        $filter = new self();
        $filter->trustAny       = false;
        $filter->trustedProxies = $proxies;
        $filter->trustedHeaders = $trustedHeaders;

        return $filter;
    }

    public function filterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '';

        if ('' === $remoteAddress) {
            // Should we trigger a warning here?
            return $request;
        }

        if (! $this->trustAny && ! $this->isFromTrustedProxy($remoteAddress)) {
            // Do nothing
            return $request;
        }

        // Update the URI based on the trusted headers
        $uri = $originalUri = $request->getUri();
        foreach ($this->trustedHeaders as $headerName) {
            $header = $request->getHeaderLine($headerName);
            if ('' === $header || false !== strpos($header, ',')) {
                // Reject empty headers and/or headers with multiple values
                continue;
            }

            switch ($headerName) {
                case self::HEADER_HOST:
                    $uri = $uri->withHost($header);
                    break;
                case self::HEADER_PORT:
                    $uri = $uri->withPort($header);
                    break;
                case self::HEADER_PROTO:
                    $uri = $uri->withScheme($header);
                    break;
                default:
                    break;
            }
        }

        if ($uri !== $originalUri) {
            return $request->withUri($uri);
        }

        return $request;
    }

    private function isFromTrustedProxy(string $remoteAddress): bool
    {
        if ($this->trustAny) {
            return true;
        }

        foreach ($this->trustedProxies as $proxy) {
            if (IPRange::matches($remoteAddress, $proxy)) {
                return true;
            }
        }

        return false;
    }

    /** @throws InvalidForwardedHeaderNameException */
    private static function validateTrustedHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            if (! in_array($header, self::X_FORWARDED_HEADERS, true)) {
                throw InvalidForwardedHeaderNameException::forHeader($header);
            }
        }
    }

    /** @throws InvalidProxyAddressException */
    private static function normalizeProxiesList($proxies): array
    {
        if (! is_array($proxies) && ! is_string($proxies)) {
            throw InvalidProxyAddressException::forInvalidProxyArgument($proxies);
        }

        $proxies = is_array($proxies) ? $proxies : [$proxies];

        foreach ($proxies as $proxy) {
            if (! self::validateProxyCIDR($proxy)) {
                throw InvalidProxyAddressException::forAddress($proxy);
            }
        }

        return $proxies;
    }

    /**
     * @param mixed $cidr
     */
    private static function validateProxyCIDR($cidr): bool
    {
        if (! is_string($cidr)) {
            return false;
        }

        $address = $cidr;
        $mask    = null;
        if (false !== strpos($cidr, '/')) {
            [$address, $mask] = explode('/', $cidr, 2);
            $mask = (int) $mask;
        }

        if (false !== strpos($address, ':')) {
            // is IPV6
            return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                && (
                    $mask === null
                    || (
                        $mask <= 128
                        && $mask >= 0
                    )
                );
        }

        // is IPV4
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && (
                $mask === null
                || (
                    $mask <= 32
                    && $mask >= 0
                )
            );
    }

    /**
     * Only allow construction via named constructors
     */
    private function __construct()
    {
    }
}
