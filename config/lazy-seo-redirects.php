<?php

return [
    'enabled' => true,

    'table' => 'seo_redirects',

    'cache_seconds' => 60,

    'preserve_query' => true,

    'wildcard_enabled' => true,

    'regex_enabled' => false,

    'allowed_status_codes' => [
        301,
        302,
        307,
        308,
        410,
    ],

    'security' => [
        'allow_external_destinations' => false,
        'allowed_hosts' => [],
        'block_protocol_relative_urls' => true,
    ],
];
