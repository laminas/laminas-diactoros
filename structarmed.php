<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Laminas\Diactoros\Exception\InvalidForwardedHeaderNameException;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;

return Architecture::define()
    ->withPreset(Preset::PSR4())
    ->layerPattern('Config', '/^Laminas\\\\Diactoros\\\\(?:ConfigProvider|Module)$/')
    ->layerPattern('Exception', '/^Laminas\\\\Diactoros\\\\Exception\\\\.*$/')
    ->layerPattern('Message', '/^Laminas\\\\Diactoros\\\\(?:HeaderSecurity|MessageTrait)$/')
    ->layerPattern('Request', '/^Laminas\\\\Diactoros\\\\(?:Request|RequestFactory|RequestTrait)$/')
    ->layerPattern(
        'Response',
        '/^Laminas\\\\Diactoros\\\\(?:Response(?:Factory)?|Response\\\\.*)$/',
        '/\\\\(?:Array)?Serializer$/'
    )
    ->layerPattern('ServerRequest', '/^Laminas\\\\Diactoros\\\\ServerRequest(?:Factory)?$/')
    ->layerPattern('ServerRequestFilter', '/^Laminas\\\\Diactoros\\\\ServerRequestFilter\\\\.*$/')
    ->layerPattern(
        'Serialization',
        [
            '/^Laminas\\\\Diactoros\\\\AbstractSerializer$/',
            '/^Laminas\\\\Diactoros\\\\Request\\\\(?:Array)?Serializer$/',
            '/^Laminas\\\\Diactoros\\\\Response\\\\(?:Array)?Serializer$/',
        ]
    )
    ->layerPattern('Stream', '/^Laminas\\\\Diactoros\\\\(?:CallbackStream|RelativeStream|Stream(?:Factory)?)$/')
    ->layerPattern('UploadedFile', '/^Laminas\\\\Diactoros\\\\UploadedFile(?:Factory)?$/')
    ->layerPattern('Uri', '/^Laminas\\\\Diactoros\\\\Uri(?:Factory)?$/')
    ->ruleset([
        'Config'              => ['Request', 'Response', 'ServerRequest', 'Stream', 'UploadedFile', 'Uri'],
        'Exception'           => [],
        'Message'             => ['Exception', 'Stream'],
        'Request'             => ['Exception', 'Message', 'Stream', 'Uri'],
        'Response'            => ['Exception', 'Message', 'Stream'],
        'Serialization'       => ['Exception', 'Request', 'Response', 'Stream', 'Uri'],
        'ServerRequest'       => ['Exception', 'Message', 'Request', 'ServerRequestFilter', 'Stream', 'Uri'],
        'ServerRequestFilter' => ['Exception', 'Uri'],
        'Stream'              => ['Exception'],
        'UploadedFile'        => ['Exception', 'Stream'],
        'Uri'                 => ['Exception'],
    ])
    ->skipPathsForRuleset(['*test*'])
    ->skipClassViolation(
        InvalidForwardedHeaderNameException::class,
        FilterUsingXForwardedHeaders::class
    );
