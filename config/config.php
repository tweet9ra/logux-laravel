<?php

return [
    /**
     * Logux password, usually LOGUX_CONTROL_PASSWORD in logux proxy project .env file
     */
    'password' => env('LOGUX_PASSWORD', 'secret'),
    /**
     * Logux proxy http endpoint
     */
    'control_url' => env('LOGUX_CONTROL_URL', 'http://localhost:31338'),
    /**
     * Logux protocol version
     */
    'protocol_version' => env('LOGUX_PROTOCOL_VERSION', 2),
    /**
     * Laravel app http endpoint path for logux
     * In your logux proxy app you must set .env LOGUX_BACKEND=http://your-laravel-app.ru/{endpoint_url}
     */
    'endpoint_url' => env('LOGUX_ENDPOINT_URL', 'logux'),
    /**
     * Middlewares for requests from logux proxy to your laravel app
     * Can be multiple separated by ,
     */
    'middleware' => false
];
