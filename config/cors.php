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
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],  // Define the paths you want CORS to apply to

    'allowed_methods' => ['*'],  // You can restrict methods like 'POST', 'GET' or use '*' for all

    'allowed_origins' => ['https://www.australianmindfulness.institute'],['https://www.canadianmindfulnessinstitute.com'],['https://www.britishmindfulnessinstitute.co.uk'],['https://www.irishmindfulnessinstitute.ie'],  // Allow specific domain

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // You can restrict headers or use '*' for all

    'exposed_headers' => false,

    'max_age' => 0,

    'supports_credentials' => false,  // Set true if you're handling authentication via cookies/sessions


];
