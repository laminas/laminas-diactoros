# Factories

[PSR-17](https://www.php-fig.org/psr/psr-17/) defines factory interfaces for
creating [PSR-7](https://www.php-fig.org/psr/psr-7/) instances. As of version
2.0.0, Diactoros supplies implementations of each as follows:

- `Laminas\Diactoros\RequestFactory`
- `Laminas\Diactoros\ResponseFactory`
- `Laminas\Diactoros\ServerRequestFactory`
- `Laminas\Diactoros\StreamFactory`
- `Laminas\Diactoros\UploadedFileFactory`
- `Laminas\Diactoros\UriFactory`

The `ServerRequestFactory` continues to define the static method
`fromGlobals()`, but also serves as a PSR-17 implementation.

These classes may be used as described in the specification document for the
purpose of creating Diactoros instances that fulfill PSR-7 typehints.

## Autoregistration of factories

- Since 2.3.0

When installing Diactoros in a Laminas or Mezzio application, or any application
using the [laminas-component-installer plugin](https://docs.laminas.dev/laminas-component-installer),
you will now be prompted to install its `ConfigProvider` and/or `Module`. When
you do, it registers the Diactoros factory implementations under the PSR-17
interface names, allowing you to compose instances of the interface in your
application classes..
