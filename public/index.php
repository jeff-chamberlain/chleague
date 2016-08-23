<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../app/vendor/autoload.php';
session_start();

spl_autoload_register(function ($classname) {
    require ("../app/classes/" . $classname . ".php");
});


$settings = include('../app/config/settings.php');
$app = new \Slim\App($settings);

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

require '../app/lib/dependencies.php';

$app->get('/authcallback', function (Request $request, Response $response) {
    $params = $request->getQueryParams();
    if (!empty($params['error'])) {

	    // Got an error, probably user denied access
	    $this->logger->addInfo('Got error: ' . $_GET['error']);

	} elseif (empty($params['code'])) {

	    $this->logger->addInfo('No code found');

	} elseif (empty($params['state']) || ($params['state'] !== $_SESSION['oauth2state'])) {

		$this->logger->addInfo('States do not match. Remote: ' . $params['state'] . ' Session: ' . $_SESSION['oauth2state']);
	    // State is invalid, possible CSRF attack in progress
	    unset($_SESSION['oauth2state']);
	    unset($_SESSION['yid']);

	} else {

	    try {

		    // Try to get an access token (using the authorization code grant)
		    $token = $this->provider->getAccessToken('authorization_code', [
		        'code' => $params['code']
		    ]);

	        // We got an access token, let's now get the owner details
	        $ownerDetails = $this->provider->getResourceOwner($token);

	        $this->users->updateUserToken(
	        	$ownerDetails->getId(),
	        	$token->getToken(),
	        	$token->getRefreshToken(),
	        	$token->getExpires()
	        );

	        //Use these details to create a new profile
	        $_SESSION['yid'] = $ownerDetails->getId();

	        $this->logger->addInfo(print_r($this->yahoo->getUserInfo($token), true));

	    } catch (Exception $e) {

	        // Failed to get user details
	        exit('Something went wrong: ' . $e->getMessage());
	    }
	}

    return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->any('/', function (Request $request, Response $response) {
	$newStream = new \GuzzleHttp\Psr7\LazyOpenStream('html/home.html', 'r');
	return $response->withBody($newStream);
})->setName('home');

$app->any('/logout', function (Request $request, Response $response) {
	unset($_SESSION['yid']);
	return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->get('/user', function (Request $request, Response $response) {

});

$app->run();
