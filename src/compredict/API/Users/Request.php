<?php

/**
 * The Request class provides a simple HTTP request interface.
 *
 *
 * Minimum requirements: PHP 5.3.x, cURL.
 *
 * @version 1.0
 * @author Ousama Esbel
 */

namespace Compredict\API\Users;

class Request
{

    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';

    // You can set the address when creating the Request object, or using the

    // Variables used for the request.
    public $userAgent = 'Mozilla/5.0 (compatible; PHP Request library)';
    public $connectTimeout = 10;
    public $timeout = 15;

    // Variables used for cookie support.
    private $cookiesEnabled = false;
    private $cookiePath;

    // Enable or disable SSL/TLS.
    private $ssl = false;

    /**
     * Request type.
     *
     * @var string
     **/
    private $requestType;

    /**
     * The data in json format.
     *
     * @var string
     **/
    private $postFields;

    /**
     * API token for authorization.
     *
     * @var string
     **/
    private $token;

    /**
     * Latency in ms.
     *
     * @var int
     **/
    private $latency;

    /**
     * Response body.
     *
     * @var std class
     **/
    private $responseBody;

    /**
     * Response header
     *
     * @var array
     **/
    private $responseHeader;

    /**
     * http status code.
     *
     * @var int
     **/
    private $httpCode;

    /**
     * Curl error.
     *
     * @var string
     **/
    private $error;

    /**
     * Base url to the server.
     *
     * @var string
     **/
    private $url;

    /**
     * Curl isntance.
     *
     * @var curl
     **/
    private $ch;

    /**
     * Last error from the server.
     *
     * @var Std class
     **/
    private $lastError;

    /**
     * Determine whether to throw error or store the error in $lastError.
     *
     * @var boolean
     **/
    private $failOnError = false;

    /**
     * The headers required for request
     * @var array
     */
    private $headers = array();

    /**
     * Called when the Request object is created.
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->lastError = false;
    }

    /**
     * Set the username and password for HTTP basic authentication.
     *
     * @param string $username
     *   Username for basic authentication.
     * @param string $password
     *   Password for basic authentication.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Throw an exception if the request encounters an HTTP error condition.
     *
     * <p>An error condition is considered to be:</p>
     *
     * <ul>
     *    <li>400-499 - Client error</li>
     *    <li>500-599 - Server error</li>
     * </ul>
     *
     * <p><em>Note that this doesn't use the builtin CURL_FAILONERROR option,
     * as this fails fast, making the HTTP body and headers inaccessible.</em></p>
     *
     * @param bool $option the new state of this feature
     */
    public function failOnError($option = true)
    {
        $this->failOnError = $option;
    }

    /**
     * Return an representation of an error returned by the last request, or false
     * if the last request was not an error.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Set the url to COMPREDICT AIC server.
     * @param String $url
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

    /**
     * Get the url to COMPREDICT AIC server
     * @return String URL
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Enable cookies.
     *
     * @param string $cookie_path
     *   Absolute path to a txt file where cookie information will be stored.
     */
    public function enableCookies($cookie_path)
    {
        $this->cookiesEnabled = true;
        $this->cookiePath = $cookie_path;
    }

    /**
     * Disable cookies.
     */
    public function disableCookies()
    {
        $this->cookiesEnabled = false;
        $this->cookiePath = '';
    }

    /**
     * @param  Boolean option to enable/disable ssl
     */
    public function verifyPeer($option)
    {
        $this->ssl = $option;
    }

    /**
     * Set timeout.
     *
     * @param int $timeout
     *   Timeout value in seconds.
     */
    public function setTimeout($timeout = 15)
    {
        $this->timeout = $timeout;
    }

    /**
     * Get timeout.
     *
     * @return int
     *   Timeout value in seconds.
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set connect timeout.
     *
     * @param int $connect_timeout
     *   Timeout value in seconds.
     */
    public function setConnectTimeout($connectTimeout = 10)
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Get connect timeout.
     *
     * @return int
     *   Timeout value in seconds.
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * Set a request type (by default, cURL will send a GET request).
     *
     * @param string $type
     *   GET, POST, DELETE, PUT, etc. Any standard request type will work.
     */
    public function setRequestType($type)
    {
        $this->requestType = $type;
    }

    /**
     * Set the POST fields (only used if $this->requestType is 'POST').
     *
     * @param array $fields
     * @param array|null $files
     *   An array of fields that will be sent with the POST request.
     */
    public function setPostFields($fields = array())
    {
        if (is_array($fields) || is_object($fields)) {
            $fields = json_encode($fields);
        }

        $this->postFields = $fields;
    }

    /**
     * Get the response body.
     *
     * @return string
     *   Response body.
     */
    public function getResponse()
    {
        return $this->responseBody;
    }

    /**
     * Get the response header.
     *
     * @return string
     *   Response header.
     */
    public function getHeader()
    {
        return $this->responseHeader;
    }

    /**
     * Get the HTTP status code for the response.
     *
     * @return int
     *   HTTP status code.
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Get the latency (the total time spent waiting) for the response.
     *
     * @return int
     *   Latency, in milliseconds.
     */
    public function getLatency()
    {
        return $this->latency;
    }

    /**
     * Get any cURL errors generated during the execution of the request.
     *
     * @return string
     *   An error message, if any error was given. Otherwise, empty.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Check for content in the HTTP response body.
     *
     * This method should not be called until after execute(), and will only check
     * for the content if the response code is 200 OK.
     *
     * @param string $content
     *   String for which the response will be checked.
     *
     * @return bool
     *   TRUE if $content was found in the response, FALSE otherwise.
     */
    public function checkResponseForContent($content = '')
    {
        if ($this->httpCode == 200 && !empty($this->responseBody)) {
            if (strpos($this->responseBody, $content) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Pipeline for POST request.
     *
     * @param string $endpoint completes the url.
     * @param string $data json encoded.
     * @param array|null $files
     * @return std class|false the result from the endpoint
     **/
    public function POST($endpoint, $data, $token = null)
    {
        $address = $this->url . $endpoint;
        $this->setRequestType(Request::POST);
        $this->setPostFields($data);
        return $this->execute($address, $token);
    }

    /**
     * Pipeline for POST request.
     *
     * @param string $endpoint completes the url.
     * @param string $data json encoded.
     * @param array|null $files
     * @return std class|false the result from the endpoint
     **/
    public function PUT($endpoint, $data, $token = null)
    {
        $address = $this->url . $endpoint;
        $this->setRequestType(Request::PUT);
        $this->setPostFields($data);
        return $this->execute($address, $token);
    }

    /**
     * Pipeline for GET request.
     *
     * @param string $endpoint completes the url.
     * @return std class|false the result from the endpoint
     **/
    public function GET($endpoint, $token = null)
    {
        $address = $this->url . $endpoint;
        $this->setRequestType(Request::GET);
        return $this->execute($address, $token);
    }

    /**
     * Check a given address with cURL.
     *
     * After this method is completed, the response body, headers, latency, etc.
     * will be populated, and can be accessed with the appropriate methods.
     */
    private function execute($address, $token = null)
    {
        // Set a default latency value.
        $latency = 0;

        // Set up cURL options.
        $this->ch = curl_init();
        // If there are basic authentication credentials, use them.
        array_push($this->headers, 'Content-Type: application/json');
        if (isset($this->token)) {
            array_push($this->headers, 'Authorization: Token ' . $this->token);
        } else
        if (!is_null($token)) {
            // allow for one time token using.
            array_push($this->headers, 'Authorization: Token ' . $token);
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        // If cookies are enabled, use them.
        if ($this->cookiesEnabled) {
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookiePath);
        }
        // Send a custom request if set (instead of standard GET).
        if (isset($this->requestType)) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->requestType);
            // If POST fields are given, and this is a POST request, add fields.
            if (($this->requestType == 'POST' || $this->requestType == 'PUT') && isset($this->postFields)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
            }
        }
        // Don't print the response; return it from curl_exec().
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_URL, $address);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        // Follow redirects (maximum of 5).
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        // SSL support.
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl);
	    curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->ssl ? 2 : 0);
        // Set a custom UA string so people can identify our requests.
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        // Output the header in the response.
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        $response = curl_exec($this->ch);
        $error = curl_error($this->ch);
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $time = curl_getinfo($this->ch, CURLINFO_TOTAL_TIME);

        // Set the header, response, error and http code.
        $this->responseHeader = substr($response, 0, $header_size);
        $this->responseBody = json_decode(substr($response, $header_size));
        $this->error = $error;
        $this->httpCode = $http_code;
        $this->headers = [];

        // Convert the latency to ms.
        $this->latency = round($time * 1000);
        return $this->handleResponse();
    }

    /**
     * Check the response for possible errors and handle the response body returned.
     *
     * If failOnError is true, a client or server error is raised, otherwise returns false
     * on error.
     */
    private function handleResponse()
    {
        if (curl_errno($this->ch)) {
            throw new NetworkError(curl_error($this->ch), curl_errno($this->ch));
        }

        if ($this->httpCode >= 400 && $this->httpCode <= 499) {
            if ($this->failOnError) {
                throw new ClientError($this->responseBody, $this->httpCode);
            } else {
                $this->lastError = $this->responseBody;
                return false;
            }
        } elseif ($this->httpCode >= 500 && $this->httpCode <= 599) {
            if ($this->failOnError) {
                throw new ServerError($this->responseBody, $this->httpCode);
            } else {
                $this->lastError = $this->responseBody;
                return false;
            }
        }

        return $this->responseBody;
    }
}
