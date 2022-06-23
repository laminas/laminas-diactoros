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

        $filter = new XForwardedHeaderFilter();

        if (empty($config)) {
            return $filter;
        }

        if (array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUST_ANY, $config)
            && $config[ConfigProvider::LEGACY_X_FORWARDED_TRUST_ANY]
        ) {
            $filter->trustAny();
            return $filter;
        }

        $proxies = array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_PROXIES, $config)
            ? $config[ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_PROXIES]
            : [];

        if ((! is_string($proxies) && ! is_array($proxies))
            || empty($proxies)
        ) {
            // Makes no sense to set trusted headers if no proxies are trusted
            return $filter;
        }

        $headers = array_key_exists(ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_HEADERS, $config)
            ? $config[ConfigProvider::LEGACY_X_FORWARDED_TRUSTED_HEADERS]
            : XForwardedHeaderFilter::X_FORWARDED_HEADERS;

        if (! is_array($headers)) {
            // Invalid value
            return $filter;
        }

        // Empty headers list implies trust all
        $headers = empty($headers) ? XForwardedHeaderFilter::X_FORWARDED_HEADERS : $headers;

        $filter->trustProxies($proxies, $headers);

        return $filter;
    }
}
