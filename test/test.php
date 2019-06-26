<?php
namespace Compredict\Test;
include 'autoloader.php';

use \Compredict\API\Users\Resources\Task as Task;
use \Compredict\API\Users\Client as Compredict;
use \Compredict\API\Users\Resources\User as User;
use \Dotenv\Dotenv;

$dotenv = new Dotenv(__DIR__ . '\..');
$dotenv->load();

$token= getenv("COMPREDICT_USERS_ADMIN_KEY", null);
$failOnError= getenv("COMPREDICT_USERS_FAIL_ON_ERROR", true);


$client = Compredict::getInstance($token);
$client->failOnError(false);


// login user
// $user = User::login(...);
// echo $user->first_name;
// $user->first_name = "...";
// $user->organization = "...";
// $user->phone_number = "blah blah blah";
// $user->update();
// var_dump($user->first_name);

// reset password:
// var_dump($client->resetPassword("baka@s"));

// registering user
// $user = User::register(...);
