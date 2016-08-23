<?php

use Psr\Log\LoggerInterface;

class UserController
{
    private $logger;
    protected $table;

    public function __construct (
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * Show a list of all users.
     *
     * @return Array
     */
    public function index()
    {
        $users = User::all();

        return $users;
    }

    /**
     * Get a user by their Yahoo ID
     *  
     * @return User
     */
    public function getUser( $yid )
    {
        return \User::find($yid);
    }

    public function updateUserToken ( $yid, $token, $refreshToken, $expiration )
    {
        $user = \User::firstOrNew(['yid' => $yid]);
        $user->access_token = $token;
        $user->refresh_token = $refreshToken;
        $user->token_expiration = $expiration;
        $user->save();
    }

    public function updateUserInfo ( $yid, $team_key, $team_name, $team_logo )
    {

    }
}