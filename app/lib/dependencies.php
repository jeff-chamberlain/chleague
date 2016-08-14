<?php

$app->add(function ($request, $response, $next) {
    $response = $next($request, $response);
    if(empty($_SESSION['yid']))
    {
        $authurl = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        $this->logger->addInfo('Sent State: ' . $_SESSION['oauth2state']);
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
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// $container['db'] = function ($c) {
//     $db = $c['settings']['db'];
//     $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
//         $db['user'], $db['pass']);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//     return $pdo;
// };

// Service factory for the ORM
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};