<?php
namespace Compredict\API\Users;

/**
 * Base class for API exceptions. Used if failOnError is true.
 */
class Error extends \Exception
{
    public function __construct($response, $code)
    {
        $message = (isset($response->errors)) ? $response->errors[0] : (isset($response->error) ? $response->error : (is_string($response) ? $response : null));
        parent::__construct($message, $code);
    }
}
