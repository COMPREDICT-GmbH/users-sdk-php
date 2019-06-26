COMPREDICT's Users API Client
===============================

PHP client for connecting to the COMPREDICT V1 REST API.

To find out more, visit the official documentation website:
https://compredict.de

Requirements
------------

- PHP 7.0 or greater
- cUrl extension enabled

**To connect to the API with basic auth you need the following:**

- (Optional) Admin API Key to register to Users. As we only allow administrative registration.

Installation
------------

Use the following Composer command to install the
API client from [the COMPREDICT vendor on Packagist](https://packagist.org/packages/compredict/users-sdk):

~~~shell
 $ composer require compredict/users-sdk
 $ composer update
~~~

Namespace
---------

All the examples below assume the `Compredict\API\Users\Client` class is imported
into the scope with the following namespace declaration:

~~~php
use Compredict\API\Users\Client as Compredict;
~~~

Configuration
-------------

To use the API client in your PHP code, ensure that you can access `Compredict\API`
in your autoload path (using Composer’s `vendor/autoload.php` hook is recommended).

Provide your credentials to the static configuration hook to prepare the API client
for connecting to a store on the COMPREDICT platform:

### Basic Auth
~~~php
$compredict_client = Compredict::getInstance(
    'Admin-token', // optional, only needed if the implementers needs to register new users.
);
~~~

Login user (GET)
--------------------------

To list all the algorithms in a collection:

~~~php
$user = $compredict_client->loginUser($username, $password);

# another option

use Compredict\API\Users\Resources\User as User;

$user = User::login($username, $password);

~~~

To get user information:

~~~php
$user->first_name;
$user->last_name;
$user->organization;
$user->phone_number;
$user->email;
$user->apiKey;
~~~


User (GET/PUT)
--------------------------

You can refresh user information by requesting the user info again through API:

~~~php
$user->refersh();
~~~

Additionally you can update the user information by requesting the user:

~~~php
$user->first_name = "Ousama";
$user->last_name = "Esbel";
$user->phone_number = "00491**********";
$user->update();
~~~

If the update fails, then the changes you made will be overridden with the previous values!


Reset Password (POST)
-----------------------------

To reset a user's password, you should pass the user's Email. An email will be received where it will redirect the user to form to rechange the password.

~~~php
$compredict_client->resetPassword($email);
~~~


Register new User (POST)
-----------------------------

In order to register new user, you should have administrative role where you should provide a token to Client instance or pass it through function `setAdminKey@Client`.

~~~php
$compredict_client = Compredict::getInstance($token);

// or

$compredict_client = Compredict::getInstance();
$compredict_client->setAdminKey($API_KEY);
~~~

then you can either do:

~~~
$user = $compredict_client->registerUser($username, $email, $password1, $password2, $organization, $first_name=null, $last_name=null, $phone_number=null);
~~~

of use the static function is User:

~~~
$user = User::register($username, $email, $password1, $password2, $organization, $first_name=null, $last_name=null, $phone_number=null)
~~~

Handling Errors And Timeouts
----------------------------

For whatever reason, the HTTP requests at the heart of the API may not always
succeed.

Every method will return false if an error occurred, and you should always
check for this before acting on the results of the method call.

In some cases, you may also need to check the reason why the request failed.
This would most often be when you tried to save some data that did not validate
correctly.

~~~php
$algorithms = $compredict_client->getAlgorithms();

if (!$algorithms) {
    $error = $compredict_client->getLastError();
    echo $error->code;
    echo $error->message;
}
~~~

Returning false on errors, and using error objects to provide context is good
for writing quick scripts but is not the most robust solution for larger and
more long-term applications.

An alternative approach to error handling is to configure the API client to
throw exceptions when errors occur. Bear in mind, that if you do this, you will
need to catch and handle the exception in code yourself. The exception throwing
behavior of the client is controlled using the failOnError method:

~~~php
$compredict_client->failOnError();

try {
    $orders = $compredict_client->getAlgorithms();

} catch(Compredict\API\Error $error) {
    echo $error->getCode();
    echo $error->getMessage();
}
~~~

The exceptions thrown are subclasses of Error, representing
client errors and server errors. The API documentation for response codes
contains a list of all the possible error conditions the client may encounter.


Verifying SSL certificates
--------------------------

By default, the client will attempt to verify the SSL certificate used by the
COMPREDICT AI Core. In cases where this is undesirable, or where an unsigned
certificate is being used, you can turn off this behavior using the verifyPeer
switch, which will disable certificate checking on all subsequent requests:

~~~php
$compredict_client->verifyPeer(false);
~~~