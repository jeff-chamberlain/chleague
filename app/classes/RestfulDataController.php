<?php

class RestfulDataController
{
	protected $container;

	public function __construct ( $container )
	{
		$this->container = $container;
	}

	public function user ( $request, $response, $args ) {
    	$data = array(
    		"loggedin" => true,
    		"user" => $this->container->get('users')->getUser($_SESSION['yid'])
		);
		return $response->withJson($data);
   }
}
