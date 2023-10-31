<?php
return [
    'api_key' => env('api_key'),
    'films_per_page' => env('films_per_page'),
    'paths' => [
        'avatar' => env('AVATAR_PATH', 'public/avatars/'),
    ],
    'academy' => [
        'base_url' => env('ACADEMY_BASE_URL'),
        'caching_time' => env('MOVIE_CACHING_TIME'),
    ],
    'omdb' => [
        'base_url' => env('OMDB_BASE_URL'),
        'api_key' => env('OMDB_API_KEY'),
        'caching_time' => env('MOVIE_CACHING_TIME'),
    ],

];
