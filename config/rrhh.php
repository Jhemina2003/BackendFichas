<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RRHH Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con el servicio de RRHH
    | para autenticación y obtención de datos de usuarios
    |
    */

    // URLs del servicio RRHH
    'base_url' => env('RRHH_BASE_URL', 'http://localhost:3000'),
    'endpoints' => [
        'signin' => env('RRHH_SIGNIN_ENDPOINT', '/api/v1/auth/signin'),
        'persona' => env('RRHH_PERSONA_ENDPOINT', '/api/v1/personas'),
        'organizacion' => env('RRHH_ORGANIZACION_ENDPOINT', '/api/v1/organizaciones'),
    ],

    // Configuración de autenticación
    'auth' => [
        'api_key' => env('RRHH_API_KEY'),
        'timeout' => env('RRHH_TIMEOUT', 30), // segundos
        'retries' => env('RRHH_RETRIES', 3),
    ],

    // Configuración de cache
    'cache' => [
        'enabled' => env('RRHH_CACHE_ENABLED', true),
        'ttl' => env('RRHH_CACHE_TTL', 3600), // 1 hora
        'prefix' => 'rrhh_',
    ],

    // Configuración de fallback
    'fallback' => [
        'enabled' => env('RRHH_FALLBACK_ENABLED', true),
        'local_auth' => env('RRHH_LOCAL_AUTH_FALLBACK', true),
    ],

    // Configuración de logging
    'logging' => [
        'enabled' => env('RRHH_LOGGING_ENABLED', true),
        'level' => env('RRHH_LOG_LEVEL', 'info'),
        'channel' => env('RRHH_LOG_CHANNEL', 'single'),
    ],

    // Configuración de sincronización
    'sync' => [
        'auto_update_users' => env('RRHH_AUTO_UPDATE_USERS', true),
        'sync_interval' => env('RRHH_SYNC_INTERVAL', 86400), // 24 horas
    ],
];