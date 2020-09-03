# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.4.1 - TBD

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.4.0 - 2020-09-02

-----

### Release Notes for [2.4.0](https://github.com/laminas/laminas-diactoros/milestone/1)

Feature release (minor)

### 2.4.0

- Total issues resolved: **0**
- Total pull requests resolved: **2**
- Total contributors: **2**




 - [49: Merge release 2.3.2 into 2.4.x](https://github.com/laminas/laminas-diactoros/pull/49) thanks to @github-actions[bot]

#### Enhancement

 - [45: Allow Streams to be instantiated using GD resources](https://github.com/laminas/laminas-diactoros/pull/45) thanks to @settermjd
## 2.3.2 - 2020-09-02

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#47](https://github.com/laminas/laminas-diactoros/pull/47) corrects the documented exception type thrown by `Laminas\Diactoros\Response\ArraySerializer::fromArray()` to indicate `Laminas\Diactoros\Exception\DeserializationException` is thrown by the method.

-----

### Release Notes for [2.3.2](https://github.com/laminas/laminas-diactoros/milestone/2)

### 2.3.2

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### Bug,Documentation

 - [47: Fixes docblock @throws in ArraySerializer::getValueFromKey()](https://github.com/laminas/laminas-diactoros/pull/47) thanks to @samsonasik
 
## 2.3.1 - 2020-07-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#44](https://github.com/laminas/laminas-diactoros/pull/44) fixes an issue whereby the uploaded file size was being provided as an integer string, and causing type errors. The value is now cast to integer before creating an `UploadedFile` instance.

## 2.3.0 - 2020-04-27

### Added

- [#37](https://github.com/laminas/laminas-diactoros/pull/37) adds a ConfigProvider and Module, allowing the package to be autoregistered within Mezzio and MVC applications. Each provides configuration mapping PSR-17 HTTP message factory interfaces to the Diactoros implementations of them.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.3 - 2020-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-diactoros:^2.2.1`.

## 2.2.2 - 2020-01-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#30](https://github.com/laminas/laminas-diactoros/pull/30) adds missing `return` statements to the various `src/functions/*.legacy.php` function files to ensure they work correctly when used.

## 2.2.1 - 2019-11-13

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#379](https://github.com/zendframework/zend-diactoros/pull/379) removes extension of `SplFileInfo` by the `UploadedFile` class. The signatures of `getSize()` are potentially incompatible, and `UploadedFile` is intended to work with arbitrary PHP and PSR-7 streams, whereas `SplFileInfo` can only model files on the filesystem. While this is technically a BC break, we are treating it as a bugfix, as the class was broken for many use cases.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.0 - 2019-11-12

### Added

- [zendframework/zend-diactoros#376](https://github.com/zendframework/zend-diactoros/pull/376) adds support for using the X-Forwarded-Host header for determining the originally requested host name when marshaling the server request.

### Changed

- [zendframework/zend-diactoros#378](https://github.com/zendframework/zend-diactoros/pull/378) updates the `UploadedFile` class to extend `SplFileInfo`, allowing developers to make use of those features in their applications.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.2.0  - 2019-11-08

### Added

- [zendframework/zend-diactoros#377](https://github.com/zendframework/zend-diactoros/issues/377) enables UploadedFile to stand in and be used as an SplFileInfo object.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.5 - 2019-10-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#372](https://github.com/zendframework/zend-diactoros/pull/372) fixes issues that occur in the `Laminas\Diactoros\Uri` class when invalid UTF-8 characters are present the user-info, path, or query string, ensuring they are URL-encoded before being consumed. Previously, such characters could result in a fatal error, which was particularly problematic when marshaling the request URI for an application request cycle.

## 2.1.4 - 2019-10-08

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#370](https://github.com/zendframework/zend-diactoros/pull/370) updates `Laminas\Diactoros\marshalHeadersFromSapi()` to ensure all underscores in header name keys are converted to dashes (fixing issues with header names such as `CONTENT_SECURITY_POLICY`, which would previously resolve improperly to `content-security_policy`).

- [zendframework/zend-diactoros#370](https://github.com/zendframework/zend-diactoros/pull/370) updates `Laminas\Diactoros\marshalHeadersFromSapi()` to ignore header names from the `$server` array that resolve to integers; previously, it would raise a fatal error.

## 2.1.3 - 2019-07-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#363](https://github.com/zendframework/zend-diactoros/issues/363) modifies detection of HTTPS schemas via the `$_SERVER['HTTPS']` value
  such that an empty HTTPS-key will result in a scheme of `http` and not
  `https`.

## 2.1.2 - 2019-04-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#355](https://github.com/zendframework/zend-diactoros/pull/355) adds `phpdbg` to the list of accepted non-SAPI enviornments for purposes
  of calling `UploadedFile::moveTo()`.

## 2.1.1 - 2019-01-05

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#349](https://github.com/zendframework/zend-diactoros/pull/349) fixes an issue when marshaling headers with values of `0` or `0` from the SAPI, ensuring they are detected and injected into the ServerRequest properly.

## 2.1.0 - 2018-12-20

### Added

- [zendframework/zend-diactoros#345](https://github.com/zendframework/zend-diactoros/pull/345) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.3 - 2019-01-05

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#349](https://github.com/zendframework/zend-diactoros/pull/349) fixes an issue when marshaling headers with values of `0` or `0` from the
  SAPI, ensuring they are detected and injected into the ServerRequest properly.

## 2.0.2 - 2018-12-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#344](https://github.com/zendframework/zend-diactoros/pull/344) provides a fix to ensure that headers with a value of "0" are retained.

## 2.0.1 - 2018-12-03

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#337](https://github.com/zendframework/zend-diactoros/pull/337) ensures that the `ServerRequestFactory::createServerRequest()` method
  creates a `php://temp` stream instead of a `php::input` stream, in compliance
  with the PSR-17 specification.

## 2.0.0 - 2018-09-27

### Added

- [zendframework/zend-diactoros#326](https://github.com/zendframework/zend-diactoros/pull/326) adds [PSR-17](https://www.php-fig.org/psr/psr-17/) HTTP Message Factory implementations, including:

  - `Laminas\Diactoros\RequestFactory`
  - `Laminas\Diactoros\ResponseFactory`
  - `Laminas\Diactoros\ServerRequestFactory`
  - `Laminas\Diactoros\StreamFactory`
  - `Laminas\Diactoros\UploadedFileFactory`
  - `Laminas\Diactoros\UriFactory`

  These factories may be used to produce the associated instances; we encourage
  users to rely on the PSR-17 factory interfaces to allow exchanging PSR-7
  implementations within their applications.

- [zendframework/zend-diactoros#328](https://github.com/zendframework/zend-diactoros/pull/328) adds a package-level exception interface, `Laminas\Diactoros\Exception\ExceptionInterface`,
  and several implementations for specific exceptions raised within the package.
  These include:

  - `Laminas\Diactoros\Exception\DeserializationException` (extends `UnexpectedValueException`)
  - `Laminas\Diactoros\Exception\InvalidArgumentException` (extends `InvalidArgumentException`)
  - `Laminas\Diactoros\Exception\InvalidStreamPointerPositionException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\SerializationException` (extends `UnexpectedValueException`)
  - `Laminas\Diactoros\Exception\UnreadableStreamException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UnrecognizedProtocolVersionException` (extends `UnexpectedValueException`)
  - `Laminas\Diactoros\Exception\UnrewindableStreamException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UnseekableStreamException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UntellableStreamException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UnwritableStreamException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UploadedFileAlreadyMovedException` (extends `RuntimeException`)
  - `Laminas\Diactoros\Exception\UploadedFileErrorException` (extends `RuntimeException`)

### Changed

- [zendframework/zend-diactoros#329](https://github.com/zendframework/zend-diactoros/pull/329) adds return type hints and scalar parameter type hints wherever possible.
  The changes were done to help improve code quality, in part by reducing manual
  type checking. If you are extending any classes, you may need to update your
  signatures; check the signatures of the class(es) you are extending for changes.

- [zendframework/zend-diactoros#162](https://github.com/zendframework/zend-diactoros/pull/162) modifies `Serializer\Request` such that it now no longer raises an `UnexpectedValueException` via its `toString()` method
  when an unexpected HTTP method is encountered; this can be done safely, as the value can never
  be invalid due to other changes in the same patch.

- [zendframework/zend-diactoros#162](https://github.com/zendframework/zend-diactoros/pull/162) modifies `RequestTrait` such that it now invalidates non-string method arguments to either
  the constructor or `withMethod()`, raising an `InvalidArgumentException` for any that do not validate.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-diactoros#308](https://github.com/zendframework/zend-diactoros/pull/308) removes the following methods from the `ServerRequestFactory` class:
  - `normalizeServer()` (use `Laminas\Diactoros\normalizeServer()` instead)
  - `marshalHeaders()` (use `Laminas\Diactoros\marshalHeadersFromSapi()` instead)
  - `marshalUriFromServer()` (use `Laminas\Diactoros\marshalUriFromSapi()` instead)
  - `marshalRequestUri()` (use `Uri::getPath()` from the `Uri` instance returned by `marshalUriFromSapi()` instead)
  - `marshalHostAndPortFromHeaders()` (use `Uri::getHost()` and `Uri::getPort()` from the `Uri` instances returned by `marshalUriFromSapi()` instead)
  - `stripQueryString()` (use `explode("?", $path, 2)[0]` instead)
  - `normalizeFiles()` (use `Laminas\Diactoros\normalizeUploadedFiles()` instead)

- [zendframework/zend-diactoros#295](https://github.com/zendframework/zend-diactoros/pull/295) removes `Laminas\Diactoros\Server`. You can use the `RequestHandlerRunner` class from
  [laminas/laminas-httphandlerrunner](https://docs.laminas.dev/laminas-httphandlerrunner) to provide these capabilities instead.

- [zendframework/zend-diactoros#295](https://github.com/zendframework/zend-diactoros/pull/295) removes `Laminas\Diactoros\Response\EmitterInterface` and the various emitter implementations.
  These can now be found in the package [laminas/laminas-httphandlerrunner](https://docs.laminas.dev/laminas-httphandlerrunner/), which also provides
  a PSR-7-implementation agnostic way of using them.

### Fixed

- Nothing.

## 1.8.7 - 2019-08-06

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#364](https://github.com/zendframework/zend-diactoros/issues/364) modifies detection of HTTPS schemas via the `$_SERVER['HTTPS']` value
  such that an empty HTTPS-key will result in a scheme of `http` and not
  `https`.

## 1.8.6 - 2018-09-05

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#325](https://github.com/zendframework/zend-diactoros/pull/325) changes the behavior of `ServerRequest::withParsedBody()`. Per
- PSR-7, it now no longer allows values other than `null`, arrays, or objects.

- [zendframework/zend-diactoros#325](https://github.com/zendframework/zend-diactoros/pull/325) changes the behavior of each of `Request`, `ServerRequest`, and
  `Response` in relation to the validation of header values. Previously, we
  allowed empty arrays to be provided via `withHeader()`; however, this was
  contrary to the PSR-7 specification. Empty arrays are no longer allowed.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#325](https://github.com/zendframework/zend-diactoros/pull/325) ensures that `Uri::withUserInfo()` no longer ignores values of
  `0` (numeric zero).

- [zendframework/zend-diactoros#325](https://github.com/zendframework/zend-diactoros/pull/325) fixes how header values are merged when calling
  `withAddedHeader()`, ensuring that array keys are ignored.

## 1.8.5 - 2018-08-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#324](https://github.com/zendframework/zend-diactoros/pull/324) fixes a reference
  to an undefined variable in the `ServerRequestFactory`, which made it
  impossible to fetch a specific header by name.

## 1.8.4 - 2018-08-01

### Added

- Nothing.

### Changed

- This release modifies how `ServerRequestFactory` marshals the request URI. In
  prior releases, we would attempt to inspect the `X-Rewrite-Url` and
  `X-Original-Url` headers, using their values, if present. These headers are
  issued by the ISAPI_Rewrite module for IIS (developed by HeliconTech).
  However, we have no way of guaranteeing that the module is what issued the
  headers, making it an unreliable source for discovering the URI. As such, we
  have removed this feature in this release of Diactoros.

  If you are developing a middleware application, you can mimic the
  functionality via middleware as follows:

  ```php
  use Psr\Http\Message\ResponseInterface;
  use Psr\Http\Message\ServerRequestInterface;
  use Psr\Http\Server\RequestHandlerInterface;
  use Laminas\Diactoros\Uri;

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
  {
      $requestUri = null;

      $httpXRewriteUrl = $request->getHeaderLine('X-Rewrite-Url');
      if ($httpXRewriteUrl !== null) {
          $requestUri = $httpXRewriteUrl;
      }

      $httpXOriginalUrl = $request->getHeaderLine('X-Original-Url');
      if ($httpXOriginalUrl !== null) {
          $requestUri = $httpXOriginalUrl;
      }

      if ($requestUri !== null) {
          $request = $request->withUri(new Uri($requestUri));
      }

      return $handler->handle($request);
  }
  ```

  If you use middleware such as the above, make sure you also instruct your web
  server to strip any incoming headers of the same name so that you can
  guarantee they are issued by the ISAPI_Rewrite module.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.8.3 - 2018-07-24

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#321](https://github.com/zendframework/zend-diactoros/pull/321) updates the logic in `Uri::withPort()` to ensure that it checks that the
  value provided is either an integer or a string integer, as only those values
  may be cast to integer without data loss.

- [zendframework/zend-diactoros#320](https://github.com/zendframework/zend-diactoros/pull/320) adds checking within `Response` to ensure that the provided reason
  phrase is a string; an `InvalidArgumentException` is now raised if it is not. This change
  ensures the class adheres strictly to the PSR-7 specification.

- [zendframework/zend-diactoros#319](https://github.com/zendframework/zend-diactoros/pull/319) provides a fix to `Laminas\Diactoros\Response` that ensures that the status
  code returned is _always_ an integer (and never a string containing an
  integer), thus ensuring it strictly adheres to the PSR-7 specification.

## 1.8.2 - 2018-07-19

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#318](https://github.com/zendframework/zend-diactoros/pull/318) fixes the logic for discovering whether an HTTPS scheme is in play
  to be case insensitive when comparing header and SAPI values, ensuring no
  false negative lookups occur.

- [zendframework/zend-diactoros#314](https://github.com/zendframework/zend-diactoros/pull/314) modifies error handling around opening a file resource within
  `Laminas\Diactoros\Stream::setStream()` to no longer use the second argument to
  `set_error_handler()`, and instead check the error type in the handler itself;
  this fixes an issue when the handler is nested inside another error handler,
  which currently has buggy behavior within the PHP engine.

## 1.8.1 - 2018-07-09

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#313](https://github.com/zendframework/zend-diactoros/pull/313) changes the reason phrase associated with the status code 425
  to "Too Early", corresponding to a new definition of the code as specified by the IANA.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#312](https://github.com/zendframework/zend-diactoros/pull/312) fixes how the `normalizeUploadedFiles()` utility function handles nested trees of
  uploaded files, ensuring it detects them properly.

## 1.8.0 - 2018-06-27

### Added

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) adds the following functions under the `Laminas\Diactoros` namespace, each of
  which may be used to derive artifacts from SAPI supergloabls for the purposes
  of generating a `ServerRequest` instance:
  - `normalizeServer(array $server, callable $apacheRequestHeaderCallback = null) : array`
    (main purpose is to aggregate the `Authorization` header in the SAPI params
    when under Apache)
  - `marshalProtocolVersionFromSapi(array $server) : string`
  - `marshalMethodFromSapi(array $server) : string`
  - `marshalUriFromSapi(array $server, array $headers) : Uri`
  - `marshalHeadersFromSapi(array $server) : array`
  - `parseCookieHeader(string $header) : array`
  - `createUploadedFile(array $spec) : UploadedFile` (creates the instance from
    a normal `$_FILES` entry)
  - `normalizeUploadedFiles(array $files) : UploadedFileInterface[]` (traverses
    a potentially nested array of uploaded file instances and/or `$_FILES`
    entries, including those aggregated under mod_php, php-fpm, and php-cgi in
    order to create a flat array of `UploadedFileInterface` instances to use in a
    request)

### Changed

- Nothing.

### Deprecated

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::normalizeServer()`; the method is
  no longer used internally, and users should instead use `Laminas\Diactoros\normalizeServer()`,
  to which it proxies.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::marshalHeaders()`; the method is
  no longer used internally, and users should instead use `Laminas\Diactoros\marshalHeadersFromSapi()`,
  to which it proxies.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::marshalUriFromServer()`; the method
  is no longer used internally. Users should use `marshalUriFromSapi()` instead.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::marshalRequestUri()`. the method is no longer
  used internally, and currently proxies to `marshalUriFromSapi()`, pulling the
  discovered path from the `Uri` instance returned by that function. Users
  should use `marshalUriFromSapi()` instead.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::marshalHostAndPortFromHeaders()`; the method
  is no longer used internally, and currently proxies to `marshalUriFromSapi()`,
  pulling the discovered host and port from the `Uri` instance returned by that
  function. Users should use `marshalUriFromSapi()` instead.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::getHeader()`; the method is no longer
  used internally. Users should copy and paste the functionality into their own
  applications if needed, or rely on headers from a fully-populated `Uri`
  instance instead.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::stripQueryString()`; the method is no longer
  used internally, and users can mimic the functionality via the expression
  `$path = explode('?', $path, 2)[0];`.

- [zendframework/zend-diactoros#307](https://github.com/zendframework/zend-diactoros/pull/307) deprecates `ServerRequestFactory::normalizeFiles()`; the functionality
  is no longer used internally, and users can use `normalizeUploadedFiles()` as
  a replacement.

- [zendframework/zend-diactoros#303](https://github.com/zendframework/zend-diactoros/pull/303) deprecates `Laminas\Diactoros\Response\EmitterInterface` and its various implementations. These are now provided via the
  [laminas/laminas-httphandlerrunner](https://docs.laminas.dev/laminas-httphandlerrunner) package as 1:1 substitutions.

- [zendframework/zend-diactoros#303](https://github.com/zendframework/zend-diactoros/pull/303) deprecates the `Laminas\Diactoros\Server` class. Users are directed to the `RequestHandlerRunner` class from the
  [laminas/laminas-httphandlerrunner](https://docs.laminas.dev/laminas-httphandlerrunner) package as an alternative.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.7.2 - 2018-05-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#301](https://github.com/zendframework/zend-diactoros/pull/301) adds stricter comparisons within the `uri` class to ensure non-empty
  values are not treated as empty.

## 1.7.1 - 2018-02-26

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#293](https://github.com/zendframework/zend-diactoros/pull/293) updates
  `Uri::getHost()` to cast the value via `strtolower()` before returning it.
  While this represents a change, it is fixing a bug in our implementation:
  the PSR-7 specification for the method, which follows IETF RFC 3986 section
  3.2.2, requires that the host name be normalized to lowercase.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#290](https://github.com/zendframework/zend-diactoros/pull/290) fixes
  `Stream::getSize()` such that it checks that the result of `fstat` was
  succesful before attempting to return its `size` member; in the case of an
  error, it now returns `null`.

## 1.7.0 - 2018-01-04

### Added

- [zendframework/zend-diactoros#285](https://github.com/zendframework/zend-diactoros/pull/285) adds a new
  custom response type, `Laminas\Diactoros\Response\XmlResponse`, for generating
  responses representing XML. Usage is the same as with the `HtmlResponse` or
  `TextResponse`; the response generated will have a `Content-Type:
  application/xml` header by default.

- [zendframework/zend-diactoros#280](https://github.com/zendframework/zend-diactoros/pull/280) adds the
  response status code/phrase pairing "103 Early Hints" to the
  `Response::$phrases` property. This is a new status proposed via
  [RFC 8297](https://datatracker.ietf.org/doc/rfc8297/).

- [zendframework/zend-diactoros#279](https://github.com/zendframework/zend-diactoros/pull/279) adds explicit
  support for PHP 7.2; previously, we'd allowed build failures, though none
  occured; we now require PHP 7.2 builds to pass.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.6.1 - 2017-10-12

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#273](https://github.com/zendframework/zend-diactoros/pull/273) updates each
  of the SAPI emitter implementations to emit the status line after emitting
  other headers; this is done to ensure that the status line is not overridden
  by PHP.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#273](https://github.com/zendframework/zend-diactoros/pull/273) modifies how
  the `SapiEmitterTrait` calls `header()` to ensure that a response code is
  _always_ passed as the third argument; this is done to prevent PHP from
  silently overriding it.

## 1.6.0 - 2017-09-13

### Added

- Nothing.

### Changed

- [zendframework/zend-diactoros#270](https://github.com/zendframework/zend-diactoros/pull/270) changes the
  behavior of `Laminas\Diactoros\Server`: it no longer creates an output buffer.

- [zendframework/zend-diactoros#270](https://github.com/zendframework/zend-diactoros/pull/270) changes the
  behavior of the two SAPI emitters in two backwards-incompatible ways:

  - They no longer auto-inject a `Content-Length` header. If you need this
    functionality, mezzio/mezzio-helpers 4.1+ provides it via
    `Mezzio\Helper\ContentLengthMiddleware`.

  - They no longer flush the output buffer. Instead, if headers have been sent,
    or the output buffer exists and has a non-zero length, the emitters raise an
    exception, as mixed PSR-7/output buffer content creates a blocking issue.
    If you are emitting content via `echo`, `print`, `var_dump`, etc., or not
    catching PHP errors or exceptions, you will need to either fix your
    application to always work with a PSR-7 response, or provide your own
    emitters that allow mixed output mechanisms.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.5.0 - 2017-08-22

### Added

- [zendframework/zend-diactoros#205](https://github.com/zendframework/zend-diactoros/pull/205) adds support
  for PHP 7.2.

- [zendframework/zend-diactoros#250](https://github.com/zendframework/zend-diactoros/pull/250) adds a new
  API to `JsonResponse` to avoid the need for decoding the response body in
  order to make changes to the underlying content. New methods include:
  - `getPayload()`: retrieve the unencoded payload.
  - `withPayload($data)`: create a new instance with the given data.
  - `getEncodingOptions()`: retrieve the flags to use when encoding the payload
    to JSON.
  - `withEncodingOptions(int $encodingOptions)`: create a new instance that uses
    the provided flags when encoding the payload to JSON.

### Changed

- [zendframework/zend-diactoros#249](https://github.com/zendframework/zend-diactoros/pull/249) changes the
  behavior of the various `Uri::with*()` methods slightly: if the value
  represents no change, these methods will return the same instance instead of a
  new one.

- [zendframework/zend-diactoros#248](https://github.com/zendframework/zend-diactoros/pull/248) changes the
  behavior of `Uri::getUserInfo()` slightly: it now (correctly) returns the
  percent-encoded values for the user and/or password, per RFC 3986 Section
  3.2.1. `withUserInfo()` will percent-encode values, using a mechanism that
  prevents double-encoding.

- [zendframework/zend-diactoros#243](https://github.com/zendframework/zend-diactoros/pull/243) changes the
  exception messages thrown by `UploadedFile::getStream()` and `moveTo()` when
  an upload error exists to include details about the upload error.

- [zendframework/zend-diactoros#233](https://github.com/zendframework/zend-diactoros/pull/233) adds a new
  argument to `SapiStreamEmitter::emit`, `$maxBufferLevel` **between** the
  `$response` and `$maxBufferLength` arguments. This was done because the
  `Server::listen()` method passes only the response and `$maxBufferLevel` to
  emitters; previously, this often meant that streams were being chunked 2 bytes
  at a time versus the expected default of 8kb.

  If you were calling the `SapiStreamEmitter::emit()` method manually
  previously, you will need to update your code.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-diactoros#205](https://github.com/zendframework/zend-diactoros/pull/205) and
  [zendframework/zend-diactoros#243](https://github.com/zendframework/zend-diactoros/pull/243) **remove
  support for PHP versions prior to 5.6 as well as HHVM**.

### Fixed

- [zendframework/zend-diactoros#248](https://github.com/zendframework/zend-diactoros/pull/248) fixes how the
  `Uri` class provides user-info within the URI authority; the value is now
  correctly percent-encoded , per RFC 3986 Section 3.2.1.

## 1.4.1 - 2017-08-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-diactoros#260](https://github.com/zendframework/zend-diactoros/pull/260) removes
  support for HHVM, as tests have failed against it for some time.

### Fixed

- [zendframework/zend-diactoros#247](https://github.com/zendframework/zend-diactoros/pull/247) fixes the
  `Stream` and `RelativeStream` `__toString()` method implementations to check
  if the stream `isSeekable()` before attempting to `rewind()` it, ensuring that
  the method does not raise exceptions (PHP does not allow exceptions in that
  method). In particular, this fixes an issue when using AWS S3 streams.

- [zendframework/zend-diactoros#252](https://github.com/zendframework/zend-diactoros/pull/252) provides a
  fix to the `SapiEmitterTrait` to ensure that any `Set-Cookie` headers in the
  response instance do not override those set by PHP when a session is created
  and/or regenerated.

- [zendframework/zend-diactoros#257](https://github.com/zendframework/zend-diactoros/pull/257) provides a
  fix for the `PhpInputStream::read()` method to ensure string content that
  evaluates as empty (including `0`) is still cached.

- [zendframework/zend-diactoros#258](https://github.com/zendframework/zend-diactoros/pull/258) updates the
  `Uri::filterPath()` method to allow parens within a URI path, per [RFC 3986
  section 3.3](https://tools.ietf.org/html/rfc3986#section-3.3) (parens are
  within the character set "sub-delims").

## 1.4.0 - 2017-04-06

### Added

- [zendframework/zend-diactoros#219](https://github.com/zendframework/zend-diactoros/pull/219) adds two new
  classes, `Laminas\Diactoros\Request\ArraySerializer` and
  `Laminas\Diactoros\Response\ArraySerializer`. Each exposes the static methods
  `toArray()` and `fromArray()`, allowing de/serialization of messages from and
  to arrays.

- [zendframework/zend-diactoros#236](https://github.com/zendframework/zend-diactoros/pull/236) adds two new
  constants to the `Response` class: `MIN_STATUS_CODE_VALUE` and
  `MAX_STATUS_CODE_VALUE`.

### Changes

- [zendframework/zend-diactoros#240](https://github.com/zendframework/zend-diactoros/pull/240) changes the
  behavior of `ServerRequestFactory::fromGlobals()` when no `$cookies` argument
  is present. Previously, it would use `$_COOKIES`; now, if a `Cookie` header is
  present, it will parse and use that to populate the instance instead.

  This change allows utilizing cookies that contain period characters (`.`) in
  their names (PHP's built-in cookie handling renames these to replace `.` with
  `_`, which can lead to synchronization issues with clients).

- [zendframework/zend-diactoros#235](https://github.com/zendframework/zend-diactoros/pull/235) changes the
  behavior of `Uri::__toString()` to better follow proscribed behavior in PSR-7.
  In particular, prior to this release, if a scheme was missing but an authority
  was present, the class was incorrectly returning a value that did not include
  a `//` prefix. As of this release, it now does this correctly.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.11 - 2017-04-06

### Added

- Nothing.

### Changes

- [zendframework/zend-diactoros#241](https://github.com/zendframework/zend-diactoros/pull/241) changes the
  constraint by which the package provides `psr/http-message-implementation` to
  simply `1.0` instead of `~1.0.0`, to follow how other implementations provide
  PSR-7.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#161](https://github.com/zendframework/zend-diactoros/pull/161) adds
  additional validations to header names and values to ensure no malformed values
  are provided.

- [zendframework/zend-diactoros#234](https://github.com/zendframework/zend-diactoros/pull/234) fixes a
  number of reason phrases in the `Response` instance, and adds automation from
  the canonical IANA sources to ensure any new phrases added are correct.

## 1.3.10 - 2017-01-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#226](https://github.com/zendframework/zend-diactoros/pull/226) fixed an
  issue with the `SapiStreamEmitter` causing the response body to be cast
  to `(string)` and also be read as a readable stream, potentially producing
  double output.

## 1.3.9 - 2017-01-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#223](https://github.com/zendframework/zend-diactoros/issues/223)
  [zendframework/zend-diactoros#224](https://github.com/zendframework/zend-diactoros/pull/224) fixed an issue
  with the `SapiStreamEmitter` consuming too much memory when producing output
  for readable bodies.

## 1.3.8 - 2017-01-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#222](https://github.com/zendframework/zend-diactoros/pull/222) fixes the
  `SapiStreamEmitter`'s handling of the `Content-Range` header to properly only
  emit a range of bytes if the header value is in the form `bytes {first-last}/length`.
  This allows using other range units, such as `items`, without incorrectly
  emitting truncated content.

## 1.3.7 - 2016-10-11

### Added

- [zendframework/zend-diactoros#208](https://github.com/zendframework/zend-diactoros/pull/208) adds several
  missing response codes to `Laminas\Diactoros\Response`, including:
  - 226 ('IM used')
  - 308 ('Permanent Redirect')
  - 444 ('Connection Closed Without Response')
  - 499 ('Client Closed Request')
  - 510 ('Not Extended')
  - 599 ('Network Connect Timeout Error')
- [zendframework/zend-diactoros#211](https://github.com/zendframework/zend-diactoros/pull/211) adds support
  for UTF-8 characters in query strings handled by `Laminas\Diactoros\Uri`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.6 - 2016-09-07

### Added

- [zendframework/zend-diactoros#170](https://github.com/zendframework/zend-diactoros/pull/170) prepared
  documentation for publication at https://docs.laminas.dev/laminas-diactoros/
- [zendframework/zend-diactoros#165](https://github.com/zendframework/zend-diactoros/pull/165) adds support
  for Apache `REDIRECT_HTTP_*` header detection in the `ServerRequestFactory`.
- [zendframework/zend-diactoros#166](https://github.com/zendframework/zend-diactoros/pull/166) adds support
  for UTF-8 characters in URI paths.
- [zendframework/zend-diactoros#204](https://github.com/zendframework/zend-diactoros/pull/204) adds testing
  against PHP 7.1 release-candidate builds.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#186](https://github.com/zendframework/zend-diactoros/pull/186) fixes a typo
  in a variable name within the `SapiStreamEmitter`.
- [zendframework/zend-diactoros#200](https://github.com/zendframework/zend-diactoros/pull/200) updates the
  `SapiStreamEmitter` to implement a check for `isSeekable()` prior to attempts
  to rewind; this allows it to work with non-seekable streams such as the
  `CallbackStream`.
- [zendframework/zend-diactoros#169](https://github.com/zendframework/zend-diactoros/pull/169) ensures that
  response serialization always provides a `\r\n\r\n` sequence following the
  headers, even when no message body is present, to ensure it conforms with RFC
  7230.
- [zendframework/zend-diactoros#175](https://github.com/zendframework/zend-diactoros/pull/175) updates the
  `Request` class to set the `Host` header from the URI host if no header is
  already present. (Ensures conformity with PSR-7 specification.)
- [zendframework/zend-diactoros#197](https://github.com/zendframework/zend-diactoros/pull/197) updates the
  `Uri` class to ensure that string serialization does not include a colon after
  the host name if no port is present in the instance.

## 1.3.5 - 2016-03-17

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#160](https://github.com/zendframework/zend-diactoros/pull/160) fixes HTTP
  protocol detection in the `ServerRequestFactory` to work correctly with HTTP/2.

## 1.3.4 - 2016-03-17

### Added

- [zendframework/zend-diactoros#119](https://github.com/zendframework/zend-diactoros/pull/119) adds the 451
  (Unavailable for Legal Reasons) status code to the `Response` class.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#117](https://github.com/zendframework/zend-diactoros/pull/117) provides
  validation of the HTTP protocol version.
- [zendframework/zend-diactoros#127](https://github.com/zendframework/zend-diactoros/pull/127) now properly
  removes attributes with `null` values when calling `withoutAttribute()`.
- [zendframework/zend-diactoros#132](https://github.com/zendframework/zend-diactoros/pull/132) updates the
  `ServerRequestFactory` to marshal the request path fragment, if present.
- [zendframework/zend-diactoros#142](https://github.com/zendframework/zend-diactoros/pull/142) updates the
  exceptions thrown by `HeaderSecurity` to include the header name and/or
  value.
- [zendframework/zend-diactoros#148](https://github.com/zendframework/zend-diactoros/pull/148) fixes several
  stream operations to ensure they raise exceptions when the internal pointer
  is at an invalid position.
- [zendframework/zend-diactoros#151](https://github.com/zendframework/zend-diactoros/pull/151) ensures
  URI fragments are properly encoded.

## 1.3.3 - 2016-01-04

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#135](https://github.com/zendframework/zend-diactoros/pull/135) fixes the
  behavior of `ServerRequestFactory::marshalHeaders()` to no longer omit
  `Cookie` headers from the aggregated headers. While the values are parsed and
  injected into the cookie params, it's useful to have access to the raw headers
  as well.

## 1.3.2 - 2015-12-22

### Added

- [zendframework/zend-diactoros#124](https://github.com/zendframework/zend-diactoros/pull/124) adds four
  more optional arguments to the `ServerRequest` constructor:
  - `array $cookies`
  - `array $queryParams`
  - `null|array|object $parsedBody`
  - `string $protocolVersion`
  `ServerRequestFactory` was updated to pass values for each of these parameters
  when creating an instance, instead of using the related `with*()` methods on
  an instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#122](https://github.com/zendframework/zend-diactoros/pull/122) updates the
  `ServerRequestFactory` to retrieve the HTTP protocol version and inject it in
  the generated `ServerRequest`, which previously was not performed.

## 1.3.1 - 2015-12-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#113](https://github.com/zendframework/zend-diactoros/pull/113) fixes an
  issue in the response serializer, ensuring that the status code in the
  deserialized response is an integer.
- [zendframework/zend-diactoros#115](https://github.com/zendframework/zend-diactoros/pull/115) fixes an
  issue in the various text-basd response types (`TextResponse`, `HtmlResponse`,
  and `JsonResponse`); due to the fact that the constructor was not
  rewinding the message body stream, `getContents()` was thus returning `null`,
  as the pointer was at the end of the stream. The constructor now rewinds the
  stream after populating it in the constructor.

## 1.3.0 - 2015-12-15

### Added

- [zendframework/zend-diactoros#110](https://github.com/zendframework/zend-diactoros/pull/110) adds
  `Laminas\Diactoros\Response\SapiEmitterTrait`, which provides the following
  private method definitions:
  - `injectContentLength()`
  - `emitStatusLine()`
  - `emitHeaders()`
  - `flush()`
  - `filterHeader()`
  The `SapiEmitter` implementation has been updated to remove those methods and
  instead compose the trait.
- [zendframework/zend-diactoros#111](https://github.com/zendframework/zend-diactoros/pull/111) adds
  a new emitter implementation, `SapiStreamEmitter`; this emitter type will
  loop through the stream instead of emitting it in one go, and supports content
  ranges.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2015-12-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#101](https://github.com/zendframework/zend-diactoros/pull/101) fixes the
  `withHeader()` implementation to ensure that if the header existed previously
  but using a different casing strategy, the previous version will be removed
  in the cloned instance.
- [zendframework/zend-diactoros#103](https://github.com/zendframework/zend-diactoros/pull/103) fixes the
  constructor of `Response` to ensure that null status codes are not possible.
- [zendframework/zend-diactoros#99](https://github.com/zendframework/zend-diactoros/pull/99) fixes
  validation of header values submitted via request and response constructors as
  follows:
  - numeric (integer and float) values are now properly allowed (this solves
    some reported issues with setting Content-Length headers)
  - invalid header names (non-string values or empty strings) now raise an
    exception.
  - invalid individual header values (non-string, non-numeric) now raise an
    exception.

## 1.2.0 - 2015-11-24

### Added

- [zendframework/zend-diactoros#88](https://github.com/zendframework/zend-diactoros/pull/88) updates the
  `SapiEmitter` to emit a `Content-Length` header with the content length as
  reported by the response body stream, assuming that
  `StreamInterface::getSize()` returns an integer.
- [zendframework/zend-diactoros#77](https://github.com/zendframework/zend-diactoros/pull/77) adds a new
  response type, `Laminas\Diactoros\Response\TextResponse`, for returning plain
  text responses. By default, it sets the content type to `text/plain;
  charset=utf-8`; per the other response types, the signature is `new
  TextResponse($text, $status = 200, array $headers = [])`.
- [zendframework/zend-diactoros#90](https://github.com/zendframework/zend-diactoros/pull/90) adds a new
  `Laminas\Diactoros\CallbackStream`, allowing you to back a stream with a PHP
  callable (such as a generator) to generate the message content. Its
  constructor accepts the callable: `$stream = new CallbackStream($callable);`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#77](https://github.com/zendframework/zend-diactoros/pull/77) updates the
  `HtmlResponse` to set the charset to utf-8 by default (if no content type
  header is provided at instantiation).

## 1.1.4 - 2015-10-16

### Added

- [zendframework/zend-diactoros#98](https://github.com/zendframework/zend-diactoros/pull/98) adds
  `JSON_UNESCAPED_SLASHES` to the default `json_encode` flags used by
  `Laminas\Diactoros\Response\JsonResponse`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#96](https://github.com/zendframework/zend-diactoros/pull/96) updates
  `withPort()` to allow `null` port values (indicating usage of default for
  the given scheme).
- [zendframework/zend-diactoros#91](https://github.com/zendframework/zend-diactoros/pull/91) fixes the
  logic of `withUri()` to do a case-insensitive check for an existing `Host`
  header, replacing it with the new one.

## 1.1.3 - 2015-08-10

### Added

- [zendframework/zend-diactoros#73](https://github.com/zendframework/zend-diactoros/pull/73) adds caching of
  the vendor directory to the Travis-CI configuration, to speed up builds.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#71](https://github.com/zendframework/zend-diactoros/pull/71) fixes the
  docblock of the `JsonResponse` constructor to typehint the `$data` argument
  as `mixed`.
- [zendframework/zend-diactoros#73](https://github.com/zendframework/zend-diactoros/pull/73) changes the
  behavior in `Request` such that if it marshals a stream during instantiation,
  the stream is marked as writeable (specifically, mode `wb+`).
- [zendframework/zend-diactoros#85](https://github.com/zendframework/zend-diactoros/pull/85) updates the
  behavior of `Laminas\Diactoros\Uri`'s various `with*()` methods that are
  documented as accepting strings to raise exceptions on non-string input.
  Previously, several simply passed non-string input on verbatim, others
  normalized the input, and a few correctly raised the exceptions. Behavior is
  now consistent across each.
- [zendframework/zend-diactoros#87](https://github.com/zendframework/zend-diactoros/pull/87) fixes
  `UploadedFile` to ensure that `moveTo()` works correctly in non-SAPI
  environments when the file provided to the constructor is a path.

## 1.1.2 - 2015-07-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#67](https://github.com/zendframework/zend-diactoros/pull/67) ensures that
  the `Stream` class only accepts `stream` resources, not any resource.

## 1.1.1 - 2015-06-25

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#64](https://github.com/zendframework/zend-diactoros/pull/64) fixes the
  behavior of `JsonResponse` with regards to serialization of `null` and scalar
  values; the new behavior is to serialize them verbatim, without any casting.

## 1.1.0 - 2015-06-24

### Added

- [zendframework/zend-diactoros#52](https://github.com/zendframework/zend-diactoros/pull/52),
  [zendframework/zend-diactoros#58](https://github.com/zendframework/zend-diactoros/pull/58),
  [zendframework/zend-diactoros#59](https://github.com/zendframework/zend-diactoros/pull/59), and
  [zendframework/zend-diactoros#61](https://github.com/zendframework/zend-diactoros/pull/61) create several
  custom response types for simplifying response creation:

  - `Laminas\Diactoros\Response\HtmlResponse` accepts HTML content via its
    constructor, and sets the `Content-Type` to `text/html`.
  - `Laminas\Diactoros\Response\JsonResponse` accepts data to serialize to JSON via
    its constructor, and sets the `Content-Type` to `application/json`.
  - `Laminas\Diactoros\Response\EmptyResponse` allows creating empty, read-only
    responses, with a default status code of 204.
  - `Laminas\Diactoros\Response\RedirectResponse` allows specifying a URI for the
    `Location` header in the constructor, with a default status code of 302.

  Each also accepts an optional status code, and optional headers (which can
  also be used to provide an alternate `Content-Type` in the case of the HTML
  and JSON responses).

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-diactoros#43](https://github.com/zendframework/zend-diactoros/pull/43) removed both
  `ServerRequestFactory::marshalUri()` and `ServerRequestFactory::marshalHostAndPort()`,
  which were deprecated prior to the 1.0 release.

### Fixed

- [zendframework/zend-diactoros#29](https://github.com/zendframework/zend-diactoros/pull/29) fixes request
  method validation to allow any valid token as defined by [RFC
  7230](http://tools.ietf.org/html/rfc7230#appendix-B). This allows usage of
  custom request methods, vs a static, hard-coded list.

## 1.0.5 - 2015-06-24

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#60](https://github.com/zendframework/zend-diactoros/pull/60) fixes
  the behavior of `UploadedFile` when the `$errorStatus` provided at
  instantiation is not `UPLOAD_ERR_OK`. Prior to the fix, an
  `InvalidArgumentException` would occur at instantiation due to the fact that
  the upload file was missing or invalid. With the fix, no exception is raised
  until a call to `moveTo()` or `getStream()` is made.

## 1.0.4 - 2015-06-23

This is a security release.

A patch has been applied to `Laminas\Diactoros\Uri::filterPath()` that ensures that
paths can only begin with a single leading slash. This prevents the following
potential security issues:

- XSS vectors. If the URI path is used for links or form targets, this prevents
  cases where the first segment of the path resembles a domain name, thus
  creating scheme-relative links such as `//example.com/foo`. With the patch,
  the leading double slash is reduced to a single slash, preventing the XSS
  vector.
- Open redirects. If the URI path is used for `Location` or `Link` headers,
  without a scheme and authority, potential for open redirects exist if clients
  do not prepend the scheme and authority. Again, preventing a double slash
  corrects the vector.

If you are using `Laminas\Diactoros\Uri` for creating links, form targets, or
redirect paths, and only using the path segment, we recommend upgrading
immediately.

### Added

- [zendframework/zend-diactoros#25](https://github.com/zendframework/zend-diactoros/pull/25) adds
  documentation. Documentation is written in markdown, and can be converted to
  HTML using [bookdown](http://bookdown.io). New features now MUST include
  documentation for acceptance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#51](https://github.com/zendframework/zend-diactoros/pull/51) fixes
  `MessageTrait::getHeaderLine()` to return an empty string instead of `null` if
  the header is undefined (which is the behavior specified in PSR-7).
- [zendframework/zend-diactoros#57](https://github.com/zendframework/zend-diactoros/pull/57) fixes the
  behavior of how the `ServerRequestFactory` marshals upload files when they are
  represented as a nested associative array.
- [zendframework/zend-diactoros#49](https://github.com/zendframework/zend-diactoros/pull/49) provides several
  fixes that ensure that Diactoros complies with the PSR-7 specification:
  - `MessageInterface::getHeaderLine()` MUST return a string (that string CAN be
    empty). Previously, Diactoros would return `null`.
  - If no `Host` header is set, the `$preserveHost` flag MUST be ignored when
    calling `withUri()` (previously, Diactoros would not set the `Host` header
    if `$preserveHost` was `true`, but no `Host` header was present).
  - The request method MUST be a string; it CAN be empty. Previously, Diactoros
    would return `null`.
  - The request MUST return a `UriInterface` instance from `getUri()`; that
    instance CAN be empty. Previously, Diactoros would return `null`; now it
    lazy-instantiates an empty `Uri` instance on initialization.
- [ZF2015-05](https://getlaminas.org/security/advisory/ZF2015-05) was
  addressed by altering `Uri::filterPath()` to prevent emitting a path prepended
  with multiple slashes.

## 1.0.3 - 2015-06-04

### Added

- [zendframework/zend-diactoros#48](https://github.com/zendframework/zend-diactoros/pull/48) drops the
  minimum supported PHP version to 5.4, to allow an easier upgrade path for
  Symfony 2.7 users, and potential Drupal 8 usage.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2015-06-04

### Added

- [zendframework/zend-diactoros#27](https://github.com/zendframework/zend-diactoros/pull/27) adds phonetic
  pronunciation of "Diactoros" to the README file.
- [zendframework/zend-diactoros#36](https://github.com/zendframework/zend-diactoros/pull/36) adds property
  annotations to the class-level docblock of `Laminas\Diactoros\RequestTrait` to
  ensure properties inherited from the `MessageTrait` are inherited by
  implementations.

### Deprecated

- Nothing.

### Removed

- Nothing.
-
### Fixed

- [zendframework/zend-diactoros#41](https://github.com/zendframework/zend-diactoros/pull/41) fixes the
  namespace for test files to begin with `LaminasTest` instead of `Laminas`.
- [zendframework/zend-diactoros#46](https://github.com/zendframework/zend-diactoros/pull/46) ensures that
  the cookie and query params for the `ServerRequest` implementation are
  initialized as arrays.
- [zendframework/zend-diactoros#47](https://github.com/zendframework/zend-diactoros/pull/47) modifies the
  internal logic in `HeaderSecurity::isValid()` to use a regular expression
  instead of character-by-character comparisons, improving performance.

## 1.0.1 - 2015-05-26

### Added

- [zendframework/zend-diactoros#10](https://github.com/zendframework/zend-diactoros/pull/10) adds
  `Laminas\Diactoros\RelativeStream`, which will return stream contents relative to
  a given offset (i.e., a subset of the stream).  `AbstractSerializer` was
  updated to create a `RelativeStream` when creating the body of a message,
  which will prevent duplication of the stream in-memory.
- [zendframework/zend-diactoros#21](https://github.com/zendframework/zend-diactoros/pull/21) adds a
  `.gitattributes` file that excludes directories and files not needed for
  production; this will further minify the package for production use cases.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-diactoros#9](https://github.com/zendframework/zend-diactoros/pull/9) ensures that
  attributes are initialized to an empty array, ensuring that attempts to
  retrieve single attributes when none are defined will not produce errors.
- [zendframework/zend-diactoros#14](https://github.com/zendframework/zend-diactoros/pull/14) updates
  `Laminas\Diactoros\Request` to use a `php://temp` stream by default instead of
  `php://memory`, to ensure requests do not create an out-of-memory condition.
- [zendframework/zend-diactoros#15](https://github.com/zendframework/zend-diactoros/pull/15) updates
  `Laminas\Diactoros\Stream` to ensure that write operations trigger an exception
  if the stream is not writeable. Additionally, it adds more robust logic for
  determining if a stream is writeable.

## 1.0.0 - 2015-05-21

First stable release, and first release as `laminas-diactoros`.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
