<?php

return [
    'name' => env('PUBLIC_SITE_NAME', 'The Archive'),
    'url' => env('PUBLIC_SITE_URL'),
    'repository_url' => env('PUBLIC_REPOSITORY_URL'),
    'commercial' => (bool) env('PUBLIC_SITE_COMMERCIAL', false),
    'tmdb' => [
        'token' => env('TMDB_API_READ_TOKEN'),
        'series_id' => env('TMDB_TV_SERIES_ID'),
        'image_base_url' => env('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p'),
        'terms_accepted' => (bool) env('TMDB_TERMS_ACCEPTED', false),
        'commercial_licensed' => (bool) env('TMDB_COMMERCIAL_LICENSED', false),
    ],
];
