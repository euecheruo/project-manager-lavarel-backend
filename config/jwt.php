<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used to sign your tokens. It should be a long, random string.
    | Usage: config('jwt.secret')
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Token Time-To-Live (TTL)
    |--------------------------------------------------------------------------
    |
    | How long (in minutes) an access token remains valid.
    | Default: 60 minutes.
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Hashing Algorithm
    |--------------------------------------------------------------------------
    |
    | The algorithm used to sign the token.
    */
    'algo' => env('JWT_ALGO', 'HS256'),
];
