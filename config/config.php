<?php

return [
    'password' => env('LOGUX_PASSWORD', 'secret'),
    'control_url' => env('LOGUX_CONTROL_URL', 'http://localhost:31338'),
    'protocol_version' => env('LOGUX_PROTOCOL_VERSION', 2),
    'endpoint_url' => env('LOGUX_ENDPOINT_URL', 'logux'),
];
