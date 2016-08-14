<?php

$appconfig = parse_ini_file('app.ini', true);

return [
    'settings' => [
        // Slim Settings
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => $appconfig['db']['db_host'],
            'database' => $appconfig['db']['db_name'],
            'username' => $appconfig['db']['db_user'],
            'password' => $appconfig['db']['db_password'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        // 'logger' => [
        //     'name' => 'slim-app',
        //     'level' => Monolog\Logger::DEBUG,
        //     'path' => __DIR__ . '../app/logs/app.log',
        // ],
        'config' => $appconfig,
    ],
];