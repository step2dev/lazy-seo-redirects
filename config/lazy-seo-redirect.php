<?php

return [
    'enabled' => env('LAZY_SEO_REDIRECTS_ENABLED', true),

    'table' => env('LAZY_SEO_REDIRECTS_TABLE', 'seo_redirects'),

    'cache_seconds' => (int) env('LAZY_SEO_REDIRECTS_CACHE_SECONDS', 60),

    'preserve_query' => env('LAZY_SEO_REDIRECTS_PRESERVE_QUERY', true),

    'wildcard_enabled' => env('LAZY_SEO_REDIRECTS_WILDCARD_ENABLED', true),

    'regex_enabled' => env('LAZY_SEO_REDIRECTS_REGEX_ENABLED', false),

    'allowed_status_codes' => [301, 302, 307, 308, 410],

    'security' => [
        'allow_external_destinations' => env('LAZY_SEO_REDIRECTS_ALLOW_EXTERNAL', false),
        'allowed_hosts' => [],
        'block_protocol_relative_urls' => true,
    ],

    'tracking' => [
        'hits' => true,
        'last_hit_at' => true,
    ],

    'routes' => [
        'web' => env('LAZY_SEO_REDIRECTS_WEB_ROUTES', false),
        'prefix' => env('LAZY_SEO_REDIRECTS_ROUTE_PREFIX', 'lazy-seo/redirects'),
        'name' => env('LAZY_SEO_REDIRECTS_ROUTE_NAME', 'lazy-seo-redirect.'),
        'middleware' => ['web'],
    ],

    'livewire' => [
        'enabled' => env('LAZY_SEO_REDIRECTS_LIVEWIRE', false),
    ],
];
