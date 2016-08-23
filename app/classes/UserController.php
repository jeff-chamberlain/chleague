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

    public function updateUser ( $yid, $token, $info )
    {
        $this->logger->addInfo("creating..." . $yid);
        try {
            $user = $this->getUser($yid);
            if ( is_null($user) )
            {
                $user = new \User;
            }
            $this->logger->addInfo("created");
            $user->yid = $yid;
            $user->access_token = $token->getToken();
            $user->refresh_token = $token->getRefreshToken();
            $user->token_expiration = $token->getExpires();
            $user->team_key = $info['team_key'];
            $user->team_name = $info['team_name'];
            $user->team_logo = $info['team_logo'];
            $this->logger->addInfo("saving...");
            $user->save();
        }
        catch(Exception $e){
           // do task when error
            $this->logger->addError($e->getMessage());
            echo $e->getMessage();   // insert query
        }
    }
}