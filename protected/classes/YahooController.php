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
		
		if(isset($teamsArray[0]))
		{
			$leagueTeam = reset(array_filter($teamsArray, function($team)
			{
				if ( isset($team['team_key']))
				{
					return strlen(strstr($team['team_key'], $this->config['league']['league_id'])) > 0;
				}
				else
				{
					return false;
				}
			}));
		}
		else if (strlen(strstr($teamsArray['team_key'], $this->config['league']['league_id'])) > 0)
		{
			$leagueTeam = $teamsArray;
		}
		

		return array(
			"team_key" => $leagueTeam['team_key'],
			"team_name" => $leagueTeam['name'],
			"team_logo" => $leagueTeam['team_logos']['team_logo']['url']
		);
	}

	public function getTeamPlayers ( $token, $teamKey )
	{
		$request  = $this->provider->getAuthenticatedRequest(
			'GET',
			self::BASE_URI . 'team/' . $teamKey . '/roster;week=' . getNFLWeek(),
			$token
		);
		$response = $this->provider->getResponse($request);
		$ob = simplexml_load_string($response);
		$json = json_encode($ob);
		$responseArray = json_decode($json, true);
		return $responseArray['team']['roster']['players']['player'];
		// return array_filter($responseArray['team']['roster']['players']['player'], function($player)
		// 	{
		// 		return $player['is_editable'];
		// 	});
	}
}
