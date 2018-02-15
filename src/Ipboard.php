<?php namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Posts;
use Alawrence\Ipboard\Topics;
use Alawrence\Ipboard\Hello;
use Alawrence\Ipboard\Members;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Mockery\CountValidator\Exception;

class Ipboard
{
    use Hello, Members, Posts, Topics;

    protected $url;
    protected $key;
    protected $reference;
    protected $httpRequest;

    /**
     * Map the HTTP status codes to exceptions.
     *
     * @var array
     */
    private $error_exceptions = [
        // General/Unknown
        520       => \Exception::class,
        // Authorization.
        "3S290/7" => Exceptions\IpboardInvalidApiKey::class,
        401       => Exceptions\IpboardInvalidApiKey::class,
        429       => Exceptions\IpboardThrottled::class,
        // Core/member
        "1C292/2" => Exceptions\IpboardMemberIdInvalid::class,
        "1C292/3" => Exceptions\IpboardMemberIdInvalid::class,
        "1C292/4" => Exceptions\IpboardMemberUsernameExists::class,
        "1C292/5" => Exceptions\IpboardMemberEmailExists::class,
        "1C292/6" => Exceptions\IpboardMemberInvalidGroup::class,
        "1C292/7" => Exceptions\IpboardMemberIdInvalid::class,
        // forums/posts
        "1F295/1" => Exceptions\IpboardForumTopicIdInvalid::class,
        "1F295/2" => Exceptions\IpboardMemberIdInvalid::class,
        "1F295/3" => Exceptions\IpboardPostInvalid::class,
        "1F295/4" => Exceptions\IpboardForumPostIdInvalid::class,
        "1F295/5" => Exceptions\IpboardForumPostIdInvalid::class,
        "2F295/6" => Exceptions\IpboardForumPostIdInvalid::class,
        "2F295/7" => Exceptions\IpboardMemberIdInvalid::class,
        "1F295/8" => Exceptions\IpboardCannotHideFirstPost::class,
        "1F295/9" => Exceptions\IpboardCannotAuthorFirstPost::class,
        "1F295/B" => Exceptions\IpboardCannotDeleteFirstPost::class,
        // torums/topics
        "1F294/1" => Exceptions\IpboardForumTopicIdInvalid::class,
        "1F294/2" => Exceptions\IpboardForumIdInvalid::class,
        "1F294/3" => Exceptions\IpboardMemberIdInvalid::class,
        "1F294/4" => Exceptions\IpboardPostInvalid::class,
        "1F294/5" => Exceptions\IpboardTopicTitleInvalid::class,
    ];

    /**
     * Construct the IPBoard API package.
     *
     * @return void
     */
    public function __construct()
    {
        $this->url = config("ipboard.api_url");
        $this->key = config("ipboard.api_key");
        $this->reference = config("ipboard.api_reference_name");

        $this->httpRequest = new HttpClient([
            "base_uri" => $this->url,
            "timeout"  => 2.0,
            "defaults" => [
                "auth" => [$this->key, ""],
            ],
            "auth"     => [$this->key, ""],
        ]);
    }

    /**
     * Perform a get request.
     *
     * @param       $function The endpoint to call via GET.
     * @param array $extra    Any query string parameters.
     *
     * @return string json return.
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     */
    private function getRequest($function, $extra = [])
    {
        return $this->request("GET", $function, ["query" => $extra]);
    }

    /**
     * Perform a post request.
     *
     * @param $function The endpoint to perform a POST request on.
     * @param $data     The form data to be sent.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     * @throws IpboardMemberInvalidGroup
     * @throws IpboardMemberUsernameExists
     * @throws IpboardMemberEmailExists
     */
    private function postRequest($function, $data)
    {
        return $this->request("POST", $function, ["form_params" => $data]);
    }

    /**
     * Perform a delete request.
     *
     * @param $function The endpoint to perform a DELETE request on.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     */
    private function deleteRequest($function)
    {
        return $this->request("DELETE", $function);
    }

    /**
     * Perform the specified request.
     *
     * @param       $method   Either GET, POST, PUT, DELETE, PATCH
     * @param       $function The endpoint to call.
     * @param array $extra    Any query string information.
     *
     * @return mixed
     * @throws \Exception
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     * @throws IpboardMemberInvalidGroup
     * @throws IpboardMemberUsernameExists
     * @throws IpboardMemberEmailExists
     */
    private function request($method, $function, $extra = [])
    {
        $response = null;
        try {
            $response = $this->httpRequest->{$method}($function, $extra)->getBody();

            return json_decode($response, false);
        } catch (ClientException $e) {
            $this->handleError($e->getResponse());
        }
    }

    /**
     * Throw the error specific to the error code that has been returned.
     *
     * All exceptions are dynamically thrown.  Where an exception doesn't exist for an error code, \Exception is thrown.
     *
     * @param $code The IPBoard error code.
     *
     * @throws \Exception
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     * @throws IpboardMemberInvalidGroup
     * @throws IpboardMemberUsernameExists
     * @throws IpboardMemberEmailExists
     */
    private function handleError($response)
    {
        $error = json_decode($response->getBody(), false);
        $errorCode = $error->errorCode;

        try {
            if (array_key_exists($errorCode, $this->error_exceptions)) {
                throw new $this->error_exceptions[$errorCode];
            }

            throw new $this->error_exceptions[$response->getStatusCode()];
        } catch (Exception $e) {
            throw new \Exception("There was a malformed response from IPBoard.");
        }
    }

}

