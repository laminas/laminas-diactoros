<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Psr\Http\Message\UriInterface;
use SensitiveParameter;
use Stringable;

use function array_keys;
use function explode;
use function filter_var;
use function implode;
use function ltrim;
use function parse_url;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_split;
use function str_starts_with;
use function strtolower;
use function substr;

use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

/**
 * Implementation of Psr\Http\UriInterface.
 *
 * Provides a value object representing a URI for HTTP requests.
 *
 * Instances of this class  are considered immutable; all methods that
 * might change state are implemented such that they retain the internal
 * state of the current instance and return a new instance that contains the
 * changed state.
 *
 * @psalm-immutable
 */
class Uri implements UriInterface, Stringable
{
    /**
     * Sub-delimiters used in user info, query strings and fragments.
     *
     * @const string
     */
    public const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters used in user info, paths, query strings, and fragments.
     *
     * @const string
     */
    public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

    /** @var int[] Array indexed by valid scheme names to their corresponding ports. */
    protected $allowedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    private string $scheme = '';

    private string $userInfo = '';

    private string $host = '';

    private ?int $port = null;

    private string $path = '';

    private string $query = '';

    private string $fragment = '';

    /**
     * generated uri string cache
     */
    private ?string $uriString = null;

    public function __construct(string $uri = '')
    {
        if ('' === $uri) {
            return;
        }

        $this->parseUri($uri);
    }

    /**
     * Operations to perform on clone.
     *
     * Since cloning usually is for purposes of mutation, we reset the
     * $uriString property so it will be re-calculated.
     */
    public function __clone()
    {
        $this->uriString = null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if (null !== $this->uriString) {
            return $this->uriString;
        }

        /** @psalm-suppress ImpureMethodCall, InaccessibleProperty */
        $this->uriString = static::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->path, // Absolute URIs should use a "/" for an empty path
            $this->query,
            $this->fragment
        );

        return $this->uriString;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->isNonStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * Retrieve the user-info part of the URI.
     *
     * This value is percent-encoded, per RFC 3986 Section 3.2.1.
     *
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->isNonStandardPort($this->scheme, $this->host, $this->port)
            ? $this->port
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        if ('' === $this->path) {
            // No path
            return $this->path;
        }

        if ($this->path[0] !== '/') {
            // Relative path
            return $this->path;
        }

        // Ensure only one leading slash, to prevent XSS attempts.
        return '/' . ltrim($this->path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme(string $scheme): UriInterface
    {
        $scheme = $this->filterScheme($scheme);

        if ($scheme === $this->scheme) {
            // Do nothing if no change was made.
            return $this;
        }

        $new         = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    // The following rule is buggy for parameters attributes
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHintSpacing.NoSpaceBetweenTypeHintAndParameter

    /**
     * Create and return a new instance containing the provided user credentials.
     *
     * The value will be percent-encoded in the new instance, but with measures
     * taken to prevent double-encoding.
     *
     * {@inheritdoc}
     */
    public function withUserInfo(
        string $user,
        #[SensitiveParameter]
        ?string $password = null
    ): UriInterface {
        $info = $this->filterUserInfoPart($user);
        if (null !== $password) {
            $info .= ':' . $this->filterUserInfoPart($password);
        }

        if ($info === $this->userInfo) {
            // Do nothing if no change was made.
            return $this;
        }

        $new           = clone $this;
        $new->userInfo = $info;

        return $new;
    }

    // phpcs:enable SlevomatCodingStandard.TypeHints.ParameterTypeHintSpacing.NoSpaceBetweenTypeHintAndParameter

    /**
     * {@inheritdoc}
     */
    public function withHost(string $host): UriInterface
    {
        if ($host === $this->host) {
            // Do nothing if no change was made.
            return $this;
        }

        $host = $this->filterHost($host);

        $new       = clone $this;
        $new->host = $host;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort(?int $port): UriInterface
    {
        if ($port === $this->port) {
            // Do nothing if no change was made.
            return $this;
        }

        if (null !== $port) {
            $port = $this->filterPort($port);
        }

        $new       = clone $this;
        $new->port = $port;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(string $path): UriInterface
    {
        if (str_contains($path, '?')) {
            throw new Exception\InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }

        if (str_contains($path, '#')) {
            throw new Exception\InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }

        $path = $this->filterPath($path);

        if ($path === $this->path) {
            // Do nothing if no change was made.
            return $this;
        }

        $new       = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery(string $query): UriInterface
    {
        if (str_contains($query, '#')) {
            throw new Exception\InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }

        $query = $this->filterQuery($query);

        if ($query === $this->query) {
            // Do nothing if no change was made.
            return $this;
        }

        $new        = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment(string $fragment): UriInterface
    {
        $fragment = $this->filterFragment($fragment);

        if ($fragment === $this->fragment) {
            // Do nothing if no change was made.
            return $this;
        }

        $new           = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * Parse a URI into its parts, and set the properties
     *
     * @psalm-suppress InaccessibleProperty Method is only called in {@see Uri::__construct} and thus immutability is
     *                                      still given.
     */
    private function parseUri(string $uri): void
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new Exception\InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }

        $this->scheme   = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = isset($parts['user']) ? $this->filterUserInfoPart($parts['user']) : '';
        $this->host     = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
        $this->port     = isset($parts['port']) ? $this->filterPort($parts['port']) : null;
        $this->path     = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query    = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    /**
     * Create a URI string from its various parts
     */
    private static function createUriString(
        string $scheme,
        string $authority,
        string $path,
        string $query,
        string $fragment
    ): string {
        $uri = '';

        if ('' !== $scheme) {
            $uri .= sprintf('%s:', $scheme);
        }

        if ('' !== $authority) {
            $uri .= '//' . $authority;
        }

        if ('' !== $path && ! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $uri .= $path;

        if ('' !== $query) {
            $uri .= sprintf('?%s', $query);
        }

        if ('' !== $fragment) {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }

    /**
     * Is a given port non-standard for the current scheme?
     */
    private function isNonStandardPort(string $scheme, string $host, ?int $port): bool
    {
        if ('' === $scheme) {
            return '' === $host || null !== $port;
        }

        if ('' === $host || null === $port) {
            return false;
        }

        return ! isset($this->allowedSchemes[$scheme]) || $port !== $this->allowedSchemes[$scheme];
    }

    /**
     * Filters the scheme to ensure it is a valid scheme.
     *
     * @param string $scheme Scheme name.
     * @return string Filtered scheme.
     */
    private function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if ('' === $scheme) {
            return '';
        }

        if (! isset($this->allowedSchemes[$scheme])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                $scheme,
                implode(', ', array_keys($this->allowedSchemes))
            ));
        }

        return $scheme;
    }

    /**
     * Filters a part of user info in a URI to ensure it is properly encoded.
     */
    private function filterUserInfoPart(string $part): string
    {
        $part = $this->filterInvalidUtf8($part);

        /**
         * @psalm-suppress ImpureFunctionCall Even tho the callback targets this immutable class,
         *                                    psalm reports an issue here.
         * Note the addition of `%` to initial charset; this allows `|` portion
         * to match and thus prevent double-encoding.
         */
        return preg_replace_callback(
            '/(?:[^%' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ']+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $part
        );
    }

    /**
     * Valid host subcomponent can be IP-literal, dotted IPv4 or reg-name
     */
    private function filterHost(string $host): string
    {
        if ($host === '') {
            return $host;
        }
        $host = strtolower($host);

        // only IP-literal is allowed colon
        if (str_contains($host, ':')) {
            /**
             * RFC3986 defines IP-literal in the host subcomponent as an IPv6 address enclosed in brackets.
             * While implementations are somewhat lenient, particularly php's parse_url(), enclosing IPv6
             * into the brackets here ensures uri authority is always valid even if assembled manually
             * outside of this implementation. This would prevent last IPv6 segment from being treated
             * as a port number.
             */
            $ipv6 = $host;
            if (str_starts_with($ipv6, '[') && str_ends_with($ipv6, ']')) {
                $ipv6 = substr($ipv6, 1, -1);
            }
            $ipv6 = filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
            if (false === $ipv6) {
                throw new Exception\InvalidArgumentException($host . ' Host contains invalid IPv6 address');
            }

            return '[' . $ipv6 . ']';
        }

        /**
         * @todo consult with interop tests for a stricter validation across implementations.
         *
         * Check for forbidden RFC3986 gen-delims = ":" / "/" / "?" / "#" / "[" / "]" / "@"
         */
        if (preg_match('~[:/?#\[\]@]~', $host)) {
            throw new Exception\InvalidArgumentException('Host contains forbidden characters');
        }

        return $host;
    }

    private function filterPort(int $port): int
    {
        if ($port < 1 || $port > 65535) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Invalid port "%d" specified; must be a valid TCP/UDP port',
                    $port
                )
            );
        }

        return $port;
    }

    /**
     * Filters the path of a URI to ensure it is properly encoded.
     */
    private function filterPath(string $path): string
    {
        $path = $this->filterInvalidUtf8($path);

        /**
         * @psalm-suppress ImpureFunctionCall Even tho the callback targets this immutable class,
         *                                    psalm reports an issue here.
         */
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ')(:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $path
        );
    }

    /**
     * Encode invalid UTF-8 characters in given string. All other characters are unchanged.
     */
    private function filterInvalidUtf8(string $string): string
    {
        // check if given string contains only valid UTF-8 characters
        if (preg_match('//u', $string)) {
            return $string;
        }

        $letters = str_split($string);
        foreach ($letters as $i => $letter) {
            if (! preg_match('//u', $letter)) {
                $letters[$i] = $this->urlEncodeChar([$letter]);
            }
        }

        return implode('', $letters);
    }

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     */
    private function filterQuery(string $query): string
    {
        if ('' !== $query && str_starts_with($query, '?')) {
            $query = substr($query, 1);
        }

        $parts = explode('&', $query);
        foreach ($parts as $index => $part) {
            [$key, $value] = $this->splitQueryValue($part);
            if ($value === null) {
                $parts[$index] = $this->filterQueryOrFragment($key);
                continue;
            }
            $parts[$index] = sprintf(
                '%s=%s',
                $this->filterQueryOrFragment($key),
                $this->filterQueryOrFragment($value)
            );
        }

        return implode('&', $parts);
    }

    /**
     * Split a query value into a key/value tuple.
     *
     * @return array A value with exactly two elements, key and value
     */
    private function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);
        if (! isset($data[1])) {
            $data[] = null;
        }
        return $data;
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     */
    private function filterFragment(string $fragment): string
    {
        if ('' !== $fragment && str_starts_with($fragment, '#')) {
            $fragment = '%23' . substr($fragment, 1);
        }

        return $this->filterQueryOrFragment($fragment);
    }

    /**
     * Filter a query string key or value, or a fragment.
     */
    private function filterQueryOrFragment(string $value): string
    {
        $value = $this->filterInvalidUtf8($value);

        /**
         * @psalm-suppress ImpureFunctionCall Even tho the callback targets this immutable class,
         *                                    psalm reports an issue here.
         */
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'urlEncodeChar'],
            $value
        );
    }

    /**
     * URL encode a character returned by a regex.
     */
    private function urlEncodeChar(array $matches): string
    {
        return rawurlencode($matches[0]);
    }
}
