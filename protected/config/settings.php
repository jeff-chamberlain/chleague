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
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],
        'config' => $appconfig,
    ],
];