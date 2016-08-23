<?php

class YahooController
{
	const BASE_URI = "https://fantasysports.yahooapis.com/fantasy/v2/";

	private $logger;

	public function __construct ( $logger, $provider ) 
	{
		$this->logger = $logger;
		$this->provider = $provider;
	}

	public function getUserInfo ( $token )
	{
		$options['headers']['Content-Type'] = 'application/json;charset=UTF-8';
		$request  = $this->provider->getAuthenticatedRequest(
			'GET',
			self::BASE_URI . 'users;use_login=1/games;game_keys=nfl/teams?format=json',
			$token->getToken(),
			$options
		);
		$response = $this->provider->getResponse($request);
		return $response;
	}
}