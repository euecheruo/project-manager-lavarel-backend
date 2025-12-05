<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    // 1. paths: Which URLs are affected?
    // We strictly only allow the 'api/*' routes to be accessed externally.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 2. allowed_methods: GET, POST, PUT, DELETE, etc.
    'allowed_methods' => ['*'],

    // 3. allowed_origins: Where is the VueJS app hosted?
    // In development, allow localhost. In production, use specific domain.
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5173', // Vite default
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    // 4. allowed_headers: CRITICAL for JWT
    // You MUST allow 'Authorization' or the Bearer token will be stripped.
    'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
