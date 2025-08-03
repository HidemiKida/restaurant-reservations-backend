<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del servidor de desarrollo
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan cómo Laravel inicia el servidor de desarrollo
    | cuando ejecutas `php artisan serve`.
    |
    */

    // Host por defecto (0.0.0.0 para escuchar en todas las interfaces)
    'host' => env('SERVER_HOST', '0.0.0.0'),
    
    // Puerto por defecto
    'port' => env('SERVER_PORT', 8000),
];