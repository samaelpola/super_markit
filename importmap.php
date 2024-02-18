<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 *
 * This file has been auto-generated by the importmap commands.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'finance' => [
        'path' => './assets/finance.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.2',
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
    'bootstrap/dist/js/bootstrap.min.js' => [
        'version' => '5.3.2',
    ],
    '@fortawesome/fontawesome-free/css/all.min.css' => [
        'version' => '6.5.1',
        'type' => 'css',
    ],
    'bootstrap/dist/js/bootstrap.bundle.min.js' => [
        'version' => '5.3.2',
    ],
    'chart.js/auto' => [
        'version' => '4.4.1',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
];
