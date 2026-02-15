<?php
// config/cors.php - Laravel 12 Format

return [
    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => [
        'api/*',
        'auth/*',
        'sanctum/csrf-cookie',
    ],

    /*
     * Matches the request method. `[*]` allows all methods.
     */
    'allowed_methods' => ['*'],

    /*
     * Matches the request origin. Wildcards can be used, eg `*.mydomain.com`
     */
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        // Add your production URLs here
        'https://hj.itiffyconsultants.xyz',
        'https://www.yourdomain.com',
    ],

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header.
     */
    'allowed_headers' => ['*'],

    /*
     * Sets the Access-Control-Expose-Headers response header.
     */
    'exposed_headers' => [],

    /*
     * Sets the Access-Control-Max-Age response header.
     */
    'max_age' => 0,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => true,
];
