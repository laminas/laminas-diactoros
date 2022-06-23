<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ConfigProvider;
use Psr\Container\ContainerInterface;

final class XForwardedRequestFilterFactory
{
    public function __invoke(ContainerInterface $container): XForwardedRequestFilter
    {
        $config = $container->get('config');
        $config = $config[ConfigProvider::CONFIG_KEY][ConfigProvider::X_FORWARDED] ?? [];

        if (! is_array($config) || empty($config)) {
            return XForwardedRequestFilter::trustNone();
        }

        if (array_key_exists(ConfigProvider::X_FORWARDED_TRUST_ANY, $config)
            && true === $config[ConfigProvider::X_FORWARDED_TRUST_ANY]
        ) {
            return XForwardedRequestFilter::trustAny();
        }

        $proxies = array_key_exists(ConfigProvider::X_FORWARDED_TRUSTED_PROXIES, $config)
            ? $config[ConfigProvider::X_FORWARDED_TRUSTED_PROXIES]
            : [];

        if ((! is_string($proxies) && ! is_array($proxies))
            || empty($proxies)
        ) {
            // Makes no sense to set trusted headers if no proxies are trusted
            return XForwardedRequestFilter::trustNone();
        }

        $headers = array_key_exists(ConfigProvider::X_FORWARDED_TRUSTED_HEADERS, $config)
            ? $config[ConfigProvider::X_FORWARDED_TRUSTED_HEADERS]
            : XForwardedRequestFilter::X_FORWARDED_HEADERS;

        if (! is_array($headers)) {
            // Invalid value
            return XForwardedRequestFilter::trustNone();
        }

        // Empty headers list implies trust all
        $headers = empty($headers) ? XForwardedRequestFilter::X_FORWARDED_HEADERS : $headers;

        return XForwardedRequestFilter::trustProxies($proxies, $headers);
    }
}
