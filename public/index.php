<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../protected/vendor/autoload.php';
session_start();

spl_autoload_register(function ($classname) {
    require ("../protected/classes/" . $classname . ".php");
});


$settings = include('../protected/config/settings.php');
$app = new \Slim\App($settings);

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

require '../protected/lib/dependencies.php';

function getNFLWeek()
{
	if ($settings['config']['dev']['in_dev_mode'])
	{
		return $settings['config']['dev']['dev_week'];
	}
	else
	{
		date_default_timezone_set('America/New_York');
		$startDate = new DateTime('2016-09-06 00:00:00');
		$interval = $startDate->diff(new DateTime());
		$days = (int)$interval->format('%a');
		return floor($days / 7) + 1;
	}
}

$app->get('/authcallback', function (Request $request, Response $response) {
	$this->logger->addInfo('Returned new user');
    $params = $request->getQueryParams();
    if (!empty($params['error'])) {

	    // Got an error, probably user denied access
	    $this->logger->addInfo('Got error: ' . $params['error']);

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
	        $userInfo = $this->yahoo->getUserInfo($token->getToken());
	        $this->logger->addInfo('So far');

	        $this->users->updateUser(
	        	$ownerDetails->getId(),
	        	$token,
	        	$userInfo
	        );

	        $this->logger->addInfo('Updated');

	        //Use these details to create a new profile
	        $_SESSION['yid'] = $ownerDetails->getId();

	    } catch (Exception $e) {

	        // Failed to get user details
	        exit('Something went wrong');
	    }
	}

    return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->any('/', function (Request $request, Response $response) {
	$newStream = new \GuzzleHttp\Psr7\LazyOpenStream('html/home.html', 'r');
	return $response->withBody($newStream);
})->setName('home');

$app->get('/login', function (Request $request, Response $response) {
	$authurl = $this->provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $this->provider->getState();
    return $response->withHeader('Location', $authurl);
});

$app->get('/logout', function (Request $request, Response $response) {
	unset($_SESSION['yid']);
	return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->group('/data', function() use ($app) {
	$app->get('/user', '\RestfulDataController:user');
	$app->get('/game', '\RestfulDataController:game');
	$app->post('/input/draft', function( $request, $response ) {
		$parsedBody = $request->getParsedBody();
		$this->logger->addInfo(print_r($parsedBody, true));
		if ( isset($parsedBody['input']) )
		{
			$user = $this->users->getUser($_SESSION['yid']);
			$drafter = \Drafter::where('team_key', $user['team_key']);

			try {
				$drafter->update(['draft_pick' => intval($parsedBody['input'])]);
			}
			catch (Exception $e) {
				$this->logger->addError($e->getMessage());
			}
		}
		else
		{
			$this->logger->addError("User tried to submit without any input!");
		}
	});
	$app->post('/input/survivor', function( $request, $response ) {
		$parsedBody = $request->getParsedBody();
		$this->logger->addInfo(print_r($parsedBody, true));
		if ( isset($parsedBody['input']) )
		{
			$user = $this->users->getUser($_SESSION['yid']);
			$survivor = \Survivor::where('team_key', $user['team_key']);

			try {
				$survivor->update(['week' . getNFLWeek() => $parsedBody['input']]);
			}
			catch (Exception $e) {
				$this->logger->addError($e->getMessage());
			}
		}
		else
		{
			$this->logger->addError("User tried to submit without any input!");
		}
	});
})->add(function ($request, $response, $next)
{
	$loggedOutResponse = $response->withJson(array("user" => array ( "loggedIn" => false )));

	if(empty($_SESSION['yid']))
    {
    	return $loggedOutResponse;
    }

    $user = $this->users->getUser($_SESSION['yid']);
    if(is_null($user))
    {
    	return $loggedOutResponse;
    }

    $token = $user->access_token;
    $this->logger->addInfo('Remaining time until token expires: ' . strval( $user->token_expiration - time() ) );
    if(time() > $user->token_expiration)
    {
    	$token = $this->provider->getAccessToken('refresh_token', [
    		"refresh_token" => $user->refresh_token	
		]);
		$this->users->refreshUserToken($_SESSION['yid'], $token);
    }

    if(!isset($token))
    {
    	return $loggedOutResponse;
    }

    $request = $request->withAttribute('token', $token);
    return $next($request, $response);
});

$app->run();
