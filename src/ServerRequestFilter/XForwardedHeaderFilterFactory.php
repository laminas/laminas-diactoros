<?php

declare(strict_types=1);

namespace Laminas\Diactoros\ServerRequestFilter;

use Laminas\Diactoros\ConfigProvider;
use Psr\Container\ContainerInterface;

final class XForwardedHeaderFilterFactory
{
    public function __invoke(ContainerInterface $container): XForwardedHeaderFilter
    {
        $config = $container->get('config');
        $config = $config[ConfigProvider::CONFIG_KEY][ConfigProvider::LEGACY_X_FORWARDED] ?? [];

        if (! is_array($config) || empty($config)) {
            return XForwardedHeaderFilter::trustNone();
        }

        if (array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUST_ANY, $config)
            && $config[ConfigProvider::LEGACY_X_FORWARDED_TRUST_ANY]
        ) {
            return XForwardedHeaderFilter::trustAny();
        }

        $proxies = array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_PROXIES, $config)
            ? $config[ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_PROXIES]
            : [];

        if ((! is_string($proxies) && ! is_array($proxies))
            || empty($proxies)
        ) {
            // Makes no sense to set trusted headers if no proxies are trusted
            return XForwardedHeaderFilter::trustNone();
        }

        $headers = array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_HEADERS, $config)
            ? $config[ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_HEADERS]
            : XForwardedHeaderFilter::X_FORWARDED_HEADERS;

        if (! is_array($headers)) {
            // Invalid value
            return XForwardedHeaderFilter::trustNone();
        }

        // Empty headers list implies trust all
        $headers = empty($headers) ? XForwardedHeaderFilter::X_FORWARDED_HEADERS : $headers;

        return XForwardedHeaderFilter::trustProxies($proxies, $headers);
    }
}
