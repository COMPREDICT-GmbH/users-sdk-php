<?php

namespace Compredict\API\Users;

use Compredict\API\Users\Resources\User as User;
use \Exception as Exception;

class Client
{
    use SingletonTrait;

    /**
     * Request instance.
     *
     * @var Request
     **/
    protected $http;

    /**
     * API token for authentication.
     *
     * @var string
     **/
    protected $adminKey;

    /**
     * Server base url
     *
     * @var string
     **/
    protected $baseURL = 'https://aic.compredict.de/api/';

    /**
     * API version
     *
     * @var string
     **/
    protected $APIVersion = 'v1';

    private function __construct($adminKey = null)
    {
        $this->http = new Request($this->baseURL . $this->APIVersion);
        $this->adminKey = $adminKey;
    }

    private function validate_token($token)
    {
        if (!isset($token) || strlen($token) !== 40) {
            throw new Exception("A 40 character API Key must be provided");
        }

        return true;
    }

    /**
     * Set the admin API key.
     */
    public function setAdminKey($token)
    {
        $this->adminKey = $token;
    }

    /**
     * Get the callback url.
     */
    public function getCallbackUrl()
    {
        return $this->callback_url;
    }

    /**
     * Set the callback url.
     */
    public function setCallbackUrl($callback_url)
    {
        if (!filter_var($callback_url, FILTER_VALIDATE_URL)) {
            throw new Exception("URL provided is not valid");
        }

        $this->callback_url = $callback_url;
    }

    /**
     * Configure the API client to throw exceptions when HTTP errors occur.
     *
     * Note that network faults will always cause an exception to be thrown.
     *
     * @param bool $option sets the value of this flag
     */
    public function failOnError($option = true)
    {
        $this->http->failOnError($option);
    }

    /**
     * Get error message returned from the last API request if
     * failOnError is false (default).
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->http->getLastError();
    }

    /**
     * @param  Boolean option to enable/disable SSL.
     */
    public function verifyPeer($option)
    {
        return $this->http->verifyPeer($option);
    }

    /**
     * Function to set the Private key that will be used to decrypt the messages.
     *
     * @param string $keyPath path to the key .ppm file.
     * @param string $passphrase for the given key.
     */
    public function setPrivateKey($keyPath, $passphrase = "")
    {
        $fp = fopen($keyPath, 'r');
        $ppk_str = fread($fp, 8192);
        fclose($fp);
        $this->ppk = openssl_pkey_get_private($ppk_str, $passphrase);
    }

    /**
     * Map a single object to a resource class.
     *
     * @param string $resource name of the resource class
     * @param \stdClass $object
     * @return Resource
     */
    private function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $class = (class_exists($baseResource)) ? $baseResource : 'Compredict\\API\\Resources\\' . $resource;
        return new $class($object);
    }

    /**
     * Internal method to wrap items in a collection to resource classes.
     *
     * @param string $resource name of the resource class
     * @param array $object object collection
     * @return array
     */
    private function mapCollection($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $resource_class = (class_exists($baseResource)) ? $baseResource : 'Compredict\\API\\Resources\\' . $resource;
        $array_of_resources = array();
        foreach ($object as $res) {
            array_push($array_of_resources, new $resource_class($object));
        }
        return $array_of_resources;
    }

    public function login($username, $password, $email = "")
    {
        if (is_null($username)) {
            throw new Exception("Username necessary!");
        }

        $request_data = ['username' => $username, 'password' => $password, 'email' => $email];
        $response = $this->http->POST("/users/login", $request_data);
        if ($response === false || is_string($response)) {
            return false;
        }

        return new User($response->key, $response->user);
    }

    public function resetPassword($email)
    {
        $request_data = ['email' => $email];
        $response = $this->http->POST('/users/password/reset', $request_data);
        return $this->http->getHttpCode() == 200;
    }

    public function getUser($api)
    {
        $response = $this->http->GET("/user", $api);
        return new User($api, $response);
    }

    public function updateUser($api, $updatedFields)
    {
        $response = $this->http->PUT('/users/', $updatedFields, $api);
        if ($response === false || is_string($response)) {
            return false;
        }

        return new User($api, $response);
    }

    public function registerUser($username, $email, $password1, $password2, $organization,
                                 $first_name = null, $last_name = null, $phone_number = null, $withVerification = true) {
        if (is_null($this->adminKey) || trim($this->adminKey) == "") {
            throw new Exception("Only admin can register user!, please provide an Admin Token Key");
        }

        $required_fields = ["username" => $username, "password1" => $password1, "password2" => $password2, "email" => $email,
            "organization" => $organization, "first_name" => $first_name, "last_name" => $last_name,
            "phone_number" => $phone_number];
        $response = $this->http->POST('/users/register', $required_fields, $this->adminKey);

        if ($response === false || is_string($response)) {
            return false;
        }

        return $withVerification ? $response->detail : new User($response->key, $response->user);
    }

    public function getUsersById($ids = array())
    {
        if (is_null($this->adminKey) || trim($this->adminKey) == "") {
            throw new Exception("Only admin can register user!, please provide an Admin Token Key");
        }

        $ids = implode(',', $ids);

        $response = $this->http->GET("/users/?ids={$ids}", $this->adminKey);
        if ($response === false || is_string($response)) {
            return false;
        }

        $users = [];
        foreach ($response as $user) {
            array_push($users, new User(null, $user));
        }
        return $users;
    }

    public function canRegister()
    {
        if (is_null($this->adminKey) || trim($this->adminKey) == "") {
            return false;
        }

        return true;
    }

}
