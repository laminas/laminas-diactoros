<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ConfigProvider;
use Psr\Container\ContainerInterface;

final class FilterUsingXForwardedHeadersFactory
{
    public function __invoke(ContainerInterface $container): FilterUsingXForwardedHeaders
    {
        $config = $container->get('config');
        $config = $config[ConfigProvider::CONFIG_KEY][ConfigProvider::X_FORWARDED] ?? [];

        if (! is_array($config) || empty($config)) {
            return FilterUsingXForwardedHeaders::trustNone();
        }

        $proxies = array_key_exists(ConfigProvider::X_FORWARDED_TRUSTED_PROXIES, $config)
            ? $config[ConfigProvider::X_FORWARDED_TRUSTED_PROXIES]
            : [];

        // '*' means trust any source as a trusted proxy for purposes of this factory
        $proxies = $proxies === '*' ? ['0.0.0.0/0'] : $proxies;

        if ((! is_string($proxies) && ! is_array($proxies))
            || empty($proxies)
        ) {
            // Makes no sense to set trusted headers if no proxies are trusted
            return FilterUsingXForwardedHeaders::trustNone();
        }

        // Missing trusted headers setting means all headers are considered trusted
        $headers = array_key_exists(ConfigProvider::X_FORWARDED_TRUSTED_HEADERS, $config)
            ? $config[ConfigProvider::X_FORWARDED_TRUSTED_HEADERS]
            : FilterUsingXForwardedHeaders::X_FORWARDED_HEADERS;

        if (! is_array($headers)) {
            // Invalid value
            return FilterUsingXForwardedHeaders::trustNone();
        }

        // Empty headers list implies trust all
        $headers = empty($headers) ? FilterUsingXForwardedHeaders::X_FORWARDED_HEADERS : $headers;

        return FilterUsingXForwardedHeaders::trustProxies($proxies, $headers);
    }
}
