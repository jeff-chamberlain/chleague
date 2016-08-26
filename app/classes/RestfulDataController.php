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
   			"players" => $this->container->get('users')->getAllUsersPublicInfo()
		);

   		if ( $game['type'] == "draft" )
   		{
   			$game['game_data'] = \Drafter::all();
   		}
   		return $response->withJson($game);
   }
}
