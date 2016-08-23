<?php

class YahooController
{
	const BASE_URI = "https://fantasysports.yahooapis.com/fantasy/v2/";

	private $logger;
	private $provider;
	private $config;

	public function __construct ( $logger, $provider, $config ) 
	{
		$this->logger = $logger;
		$this->provider = $provider;
		$this->config = $config;
	}

	public function getUserInfo ( $token )
	{
		$options['headers']['Content-Type'] = 'application/json;charset=UTF-8';
		$request  = $this->provider->getAuthenticatedRequest(
			'GET',
			self::BASE_URI . 'users;use_login=1/games;game_keys=nfl/teams',
			$token,
			$options
		);
		$response = $this->provider->getResponse($request);
		$ob = simplexml_load_string($response);
		$json = json_encode($ob);
		$responseArray = json_decode($json, true);
		$teamsArray = $responseArray['users']['user']['games']['game']['teams']['team'];
		$leagueTeam = reset(array_filter($teamsArray, function($team)
		{
			return strlen(strstr($team['team_key'], $this->config['league_id'])) > 0;
		}));

		return array(
			"team_key" => $leagueTeam['team_key'],
			"team_name" => $leagueTeam['name'],
			"team_logo" => $leagueTeam['team_logos']['team_logo']['url']
		);
	}
}
