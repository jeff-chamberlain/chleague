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
         $game['players'] = $this->container->get('yahoo')->getTeamPlayers( $request->getAttribute('token'),
            $user['team_key']);
         $game['selected_player'] = \Survivor::select('week' . getNFLWeek())->where('team_key', '=', $user['team_key'])->first()['week' . getNFLWeek()];
         if (isset($game['selected_player']))
         {
            $selectedPlayer = reset(array_filter($game['players'], function($player)
            {
               return $player['team_key'] == $game['selected_player'];
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
