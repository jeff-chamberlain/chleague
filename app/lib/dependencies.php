<?php

$container = $app->getContainer();

$container['provider'] = function($c) {
    return new Hayageek\OAuth2\Client\Provider\Yahoo([
        'clientId'     => $c->get('settings')['config']['oauth']['client_id'],
        'clientSecret' => $c->get('settings')['config']['oauth']['client_secret'],
        'redirectUri'  => $c->get('settings')['config']['oauth']['redirect_uri'],
    ]);
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['users'] = function ($container) {
    $users = new \UserController( $container->get('logger') );
    return $users;
};

$container['yahoo'] = function ($container)
{
    $yahoo = new \YahooController (
        $container->get('logger'),
        $container->get('provider'),
        $container->get('settings')['config']['league']
    );
    return $yahoo;
};