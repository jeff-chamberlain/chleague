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

require '../app/lib/dependencies.php';

$app->get('/login', function (Request $request, Response $response) {
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

	    // Try to get an access token (using the authorization code grant)
	    $token = $this->provider->getAccessToken('authorization_code', [
	        'code' => $params['code']
	    ]);

	    // Optional: Now you have a token you can look up a users profile data
	    try {

	        // We got an access token, let's now get the owner details
	        $ownerDetails = $this->provider->getResourceOwner($token);

	        //Use these details to create a new profile
	        $_SESSION['yid'] = $ownerDetails->getId();
	    } catch (Exception $e) {

	        // Failed to get user details
	        exit('Something went wrong: ' . $e->getMessage());

	    }


	    // // Use this to interact with an API on the users behalf
	    // echo "Token: ". $token->getToken()."<br>";

	    // // Use this to get a new access token if the old one expires
	    // echo  "Refresh Token: ".$token->getRefreshToken()."<br>";

	    // // Number of seconds until the access token will expire, and need refreshing
	    // echo "Expires:" .$token->getExpires()."<br>";

	}

    return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->any('/', function (Request $request, Response $response) {
	$newStream = new \GuzzleHttp\Psr7\LazyOpenStream('html/home.html', 'r');
	return $response->withBody($newStream);;
})->setName('home');

$app->run();
