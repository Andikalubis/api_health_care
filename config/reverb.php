<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Reverb Server
    |--------------------------------------------------------------------------
    |
    | This option controls the default server used by Reverb to handle
    | incoming connections as well as broadcasting events to your
    | connected clients. At this time, only one server is supported.
    |
    */

    'default' => env('REVERB_SERVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    |
    | Here you may define the configuration for each of your Reverb servers.
    | A server configuration includes the host and port that the server
    | will listen on, as well as any other necessary options.
    |
    */

    'servers' => [

        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST'),
            'import_values' => [
                'REVERB_APP_ID',
                'REVERB_APP_KEY',
                'REVERB_APP_SECRET',
            ],
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', 6379),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'pulse_ingest' => env('REVERB_PULSE_INGEST', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Applications
    |--------------------------------------------------------------------------
    |
    | Here you may define the applications that are allowed to connect to
    | your Reverb server. Each application must have a unique ID and
    | key, and may specify its own set of allowed origins.
    |
    */

    'apps' => [

        'apps' => [
            [
                'key' => env('REVERB_APP_KEY'),
                'id' => env('REVERB_APP_ID'),
                'secret' => env('REVERB_APP_SECRET'),
                'role' => 'admin',
                'allowed_origins' => ['*'],
                'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                'max_connections' => env('REVERB_APP_MAX_CONNECTIONS', 100),
                'max_channels' => env('REVERB_APP_MAX_CHANNELS', 100),
                'max_clients_per_channel' => env('REVERB_APP_MAX_CLIENTS_PER_CHANNEL', 100),
                'max_payload_size' => env('REVERB_APP_MAX_PAYLOAD_SIZE', 10_000),
            ],
        ],

    ],

];
