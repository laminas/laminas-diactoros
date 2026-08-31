<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

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
        'Exception'           => ['ServerRequestFilter'],
        'Message'             => ['+Stream'],
        'Request'             => ['+Message', 'Uri'],
        'Response'            => ['+Message'],
        'Serialization'       => ['Exception', 'Request', 'Response', 'Stream', 'Uri'],
        'ServerRequest'       => ['+Request', 'ServerRequestFilter'],
        'ServerRequestFilter' => ['+Uri'],
        'Stream'              => ['Exception'],
        'UploadedFile'        => ['+Stream'],
        'Uri'                 => ['Exception'],
    ])
    ->skipPathsForRuleset(['*test*']);
