<?php

declare(strict_types=1);

namespace Laminas\Diactoros;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ConfigProvider
{
    public const CONFIG_KEY = 'laminas-diactoros';
    public const LEGACY_X_FORWARDED = 'laminas-x-forwarded-header-filter';
    public const LEGACY_X_FORWARDED_TRUST_ANY = 'trust-any';
    public const LEGACY_X_FORWARDED_TRUSTED_PROXIES = 'trusted-proxies';
    public const LEGACY_X_FORWARDED_TRUSTED_HEADERS = 'trusted-headers';

    /**
     * Retrieve configuration for laminas-diactoros.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            self::CONFIG_KEY => $this->getComponentConfig(),
        ];
    }

    /**
     * Returns the container dependencies.
     * Maps factory interfaces to factories.
     */
    public function getDependencies() : array
    {
        // @codingStandardsIgnoreStart
        return [
            'invokables' => [
                RequestFactoryInterface::class => RequestFactory::class,
                ResponseFactoryInterface::class => ResponseFactory::class,
                StreamFactoryInterface::class => StreamFactory::class,
                ServerRequestFactoryInterface::class => ServerRequestFactory::class,
                ServerRequestFilter\LegacyXForwardedHeaderFilter::class => ServerRequestFilter\LegacyXForwardedHeaderFilterFactory::class,
                UploadedFileFactoryInterface::class => UploadedFileFactory::class,
                UriFactoryInterface::class => UriFactory::class
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function getComponentConfig(): array
    {
        return [
            self::LEGACY_X_FORWARDED => [
                self::LEGACY_X_FORWARDED_TRUST_ANY       => false,
                self::LEGACY_X_FORWARDED_TRUSTED_PROXIES => [],
                self::LEGACY_X_FORWARDED_TRUSTED_HEADERS => [],
            ],
        ];
    }
}
