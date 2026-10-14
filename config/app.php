<?php

return [
    'name' => env('APP_NAME', 'Manager Library'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'es',
    'fallback_locale' => 'es',
    'faker_locale' => 'es_CO',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
];
