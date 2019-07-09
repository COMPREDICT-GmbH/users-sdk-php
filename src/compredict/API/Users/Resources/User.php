<?php

namespace Compredict\API\Users\Resources;

use Compredict\API\Users\Client as Client;
use Compredict\API\Users\Resource as Resource;

class User extends Resource
{

    public $APIKey;

    public function __construct($APIKey, $object = false)
    {
        parent::__construct($object);
        $this->APIKey = $APIKey;
    }

    public function update()
    {
        if (!isset($this->APIKey)) {
            throw new Exception("User is not logged in");
        }

        //var_dump($this->fields);
        $user = $this->client->updateUser($this->APIKey, $this->fields);
        if ($user === false) {
            $this->refresh();
        } else {
            $this->fields = $user->fields;
        }

        return $this;
    }

    public function refresh()
    {
        if (!isset($this->APIKey)) {
            throw new Exception("User is not logged in");
        }

        $user = $this->client->getUser($this->APIKey);
        $this->fields = $user->fields;
        return $this;
    }

    public function fresh()
    {
        if (!isset($this->APIKey)) {
            throw new Exception("User is not logged in");
        }

        $user = $this->client->getUser($this->APIKey);
        return $user;
    }

    public static function login($username, $password, $email = "")
    {
        $client = Client::getInstance();
        $user = $client->login($username, $password, $email);
        return $user;
    }

    public static function register($username, $email, $password1, $password2, $organization,
        $first_name = null, $last_name = null, $phone_number = null) {
        $client = Client::getInstance();
        $user = $client->registerUser($username, $email, $password1, $password2, $organization,
            $first_name, $last_name, $phone_number);
        return $user;
    }
}
