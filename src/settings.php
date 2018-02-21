<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // password hashing salt
        'salt' => '12345-CHANGEME-12345-CHANGEME',

        'jwt' => [
            'secret' => '214f7af41ec278ee5c0ea607f50182374c45c5c1', // jwt token secret
            'issuer' => 'http://example.com',
            'audience' => 'http://example.com'
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        // Database settings
        'database' => [
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
            'dbname'   => 'webdev',
            'user'     => 'userdev',
            'password' => 'userdev',
        ],
    ],
];
