<?php

class RestfulDataController
{
	protected $container;

	public function __construct ( $container )
	{
		$this->container = $container;
	}

	public function user ( $request, $response, $args ) {
		$user = $this->container->get('users')->getPublicUserInfo($_SESSION['yid']);
		$user['loggedIn'] = true;
		return $response->withJson($user);
   }

   public function game ( $request, $response, $args ) {
		$game = array(
			"type" => $this->container->get('settings')['config']['game']['type'],
			// "players" => $this->container->get('users')->getAllUsersPublicInfo()
		);

		if ( $game['type'] == "draft" )
		{
			$game['game_data'] = \Drafter::all();
		}
      else if ( $game['type'] == "survivor" )
      {
         $user = $this->container->get('users')->getPublicUserInfo($_SESSION['yid']);
         $possiblePlayers = $this->container->get('yahoo')->getTeamPlayers( $request->getAttribute('token'),
            $user['team_key']);
         $game['eliminated'] = \Survivor::select('eliminated')->where('team_key', '=', $user['team_key'])->first()['eliminated'];

         if(!$game['eliminated'])
         {
            $chosenPlayers = \Survivor::select('week3', 'week4', 'week5', 'week6')->where('team_key', '=', $user['team_key'])->first();
            
            $chosenPlayerKeys = array();

            for( $i = 3; $i < getNFLWeek($this->container->get('settings')['config']); $i++)
            {
               array_push($chosenPlayerKeys, $chosenPlayers['week' . $i]);
            }

            $game['players'] = array_values(array_filter($possiblePlayers, function($player) use ($chosenPlayerKeys)
            {
               return $player['is_editable'] && !in_array($player['player_key'], $chosenPlayerKeys);
            }));

            $selectedPlayerKey = $chosenPlayers['week' . getNFLWeek($this->container->get('settings')['config'])];

            if (isset($selectedPlayerKey))
            {
               $game['selected_player'] = $selectedPlayerKey;

               $selectedPlayer = reset(array_filter($possiblePlayers, function($possiblePlayer) use ($selectedPlayerKey)
               {
                  return $possiblePlayer['player_key'] === $selectedPlayerKey;
               }));

               if(isset($selectedPlayer) && !$selectedPlayer['is_editable'])
               {
                  $game['players'] = array($selectedPlayer);
               }
            }
         }
      }
      else if ( $game['type'] == "tournament" )
      {
         $user = $this->container->get('users')->getPublicUserInfo($_SESSION['yid']);
         $possiblePlayers = $this->container->get('yahoo')->getTeamPlayers( $request->getAttribute('token'),
            $user['team_key']);
         $tourneyTeam = \Tourneyteam::find($user['team_key']);
         $game['eliminated'] = $tourneyTeam['eliminated'];

         if(!$game['eliminated'])
         {
            $game['players'] = array_values(array_filter($possiblePlayers, function($player)
            {
               return $player['is_editable'] && $player['selected_position']['position'] !== 'BN';
            }));

            $selectedPlayerKey = $tourneyTeam['week' . ( getNFLWeek($this->container->get('settings')['config']))];

            if (!isset($selectedPlayerKey))
            {
               $selectedPlayerKey = $tourneyTeam['week' . ( getNFLWeek($this->container->get('settings')['config']) - 1 )];
            }

            if(isset($selectedPlayerKey))
            {
               $game['selected_player'] = $selectedPlayerKey;

               $selectedPlayer = reset(array_filter($possiblePlayers, function($possiblePlayer) use ($selectedPlayerKey)
               {
                  return $possiblePlayer['player_key'] === $selectedPlayerKey;
               }));

               if(isset($selectedPlayer) && !$selectedPlayer['is_editable'])
               {
                  $game['players'] = array($selectedPlayer);
               }
            }
         }
      }
		return $response->withJson($game);
   }

   public function results ( $request, $response, $args ) {
      if ($_SESSION['yid'] != $this->container->get('settings')['config']['dev']['dev_yid'])
      {
         return $response->withJson(array());
      };
      $week = $args['week'];
      $teams = \Tourneyteam::where('eliminated', '0')->get();
      $teamIDs = array();
      $playerIDs = array();
      $results = array();

      foreach ( $teams as $team )
      {
         $id = strval($team['team_key']);
         array_push($teamIDs, $id);
         $results[$id] = array(
            "team" => [],
            "captain" => []
         );
         if (isset($team['week' . $week]))
         {
            $playerID = $team['week' . $week];
         }
         else
         {
            $prevWeek = intval($week) - 1;
            while( !isset($playerID) && array_key_exists('week' . $prevWeek, $team))
            {
               if ( isset($team['week' . $prevWeek]) )
               {
                  $playerID = $team['week' . $prevWeek];
               }
               else
               {
                  $prevWeek --;
               }
            }
         }
         if(isset($playerID))
         {
            $results[$id]['captain']['key'] = $playerID;
            array_push($playerIDs, $playerID);
            unset($playerID);
         }
      }

      $teams = $this->container->get('yahoo')->getTeamPoints( $request->getAttribute('token'), $teamIDs, $week );
      $captains = $this->container->get('yahoo')->getPlayerPoints( $request->getAttribute('token'), $teamIDs, $playerIDs, $week );

      foreach ($results as $team_key => &$data) {
         $data['team'] = $teams[$team_key];
         if(isset($data['captain']['key']))
         {
            $data['captain'] = $captains[$data['captain']['key']];
         }
      }

      $body = $response->getBody();
      $body->write('<pre>' . json_encode($results, JSON_PRETTY_PRINT) . '</pre>');
      return $response;
   }
}
