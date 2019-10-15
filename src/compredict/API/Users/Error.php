<?php
namespace Compredict\API\Users;

/**
 * Base class for API exceptions. Used if failOnError is true.
 */
class Error extends \Exception
{
    public function __construct($response, $code)
    {

        if (isset($response->errors)) {
            $message = explode(':', $response->errors[0]);
            $message = $message[sizeof($message) - 1];
        } elseif (isset($response->error)) {
            $message = $response->error;
        } elseif (is_string($response)) {
            $message = $response;
        } else {
            $message = 'Something went wrong please try again!';
        }

        parent::__construct($message, $code);
    }
}
