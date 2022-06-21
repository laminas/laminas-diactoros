<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Laminas\Diactoros\ServerRequestFilter\LegacyXForwardedHeaderFilter;
use Laminas\Diactoros\ServerRequestFilter\ServerRequestFilterInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function is_callable;

/**
 * Class for marshaling a request object from the current PHP environment.
 *
 * Logic largely refactored from the Laminas Laminas\Http\PhpEnvironment\Request class.
 *
 * @copyright Copyright (c) 2005-2015 Laminas (https://www.zend.com)
 * @license   https://getlaminas.org/license/new-bsd New BSD License
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * Function to use to get apache request headers; present only to simplify mocking.
     *
     * @var callable
     */
    private static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * The ServerRequest created is then passed to the fromServer() method in
     * order to marshal the request URI and headers.
     *
     * @see fromServer()
     * @param array $server $_SERVER superglobal
     * @param array $query $_GET superglobal
     * @param array $body $_POST superglobal
     * @param array $cookies $_COOKIE superglobal
     * @param array $files $_FILES superglobal
     * @param null|ServerRequestFilterInterface $requestFilter If present, the
     *     generated request will be passed to this instance and the result
     *     returned by this method. When not present, a default instance
     *     is created and used. For version 2, that instance is of
     *     LegacyXForwardedHeaderFilter, with the `trustAny()` method called.
     *     For version 3, it will be a NoOpRequestFilter instance.
     * @return ServerRequest
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null,
        ?ServerRequestFilterInterface $requestFilter = null
    ) : ServerRequest {
        // @todo For version 3, we should instead create a NoOpRequestFilter instance.
        if (null === $requestFilter) {
            $requestFilter = new LegacyXForwardedHeaderFilter();
            $requestFilter->trustAny();
        }

        $server = normalizeServer(
            $server ?: $_SERVER,
            is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
        );
        $files   = normalizeUploadedFiles($files ?: $_FILES);
        $headers = marshalHeadersFromSapi($server);

        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }

        return $requestFilter->filterRequest(new ServerRequest(
            $server,
            $files,
            marshalUriFromSapiSafely($server, $headers),
            marshalMethodFromSapi($server),
            'php://input',
            $headers,
            $cookies ?: $_COOKIE,
            $query ?: $_GET,
            $body ?: $_POST,
            marshalProtocolVersionFromSapi($server)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface
    {
        $uploadedFiles = [];

        return new ServerRequest(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            'php://temp'
        );
    }
}
