<?php

$app->add(function ($request, $response, $next) {
    $response = $next($request, $response);
    if(empty($_SESSION['yid']))
    {
        $authurl = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        return $response->withHeader('Location', $authurl);
    }   
    return $response;
});

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
    $users = new \UserController( $container->logger );
    return $users;
};

$container['yahoo'] = function ($container)
{
    $yahoo = new \YahooController ( $container->logger, $container->provider );
    return $yahoo;
};