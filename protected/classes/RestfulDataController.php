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

         $game['players'] = array_values(array_filter($possiblePlayers, function($player)
         {
            return $player['is_editable'];
         }));

         $selectedPlayerKey = \Survivor::select('week' . getNFLWeek())->where('team_key', '=', $user['team_key'])->first()['week' . getNFLWeek()];

         if (isset($selectedPlayerKey))
         {
            $game['selected_player'] = $selectedPlayerKey;
            $this->container->logger->addInfo('PRE: ' . $selectedPlayerKey);

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
		return $response->withJson($game);
   }
}
