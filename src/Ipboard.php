<?php namespace Alawrence\Ipboard;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

class Ipboard
{
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
        "1C292/2" => Exceptions\IpboardMemberIDInvalid::class,
        "1C292/3" => Exceptions\IpboardMemberIDInvalid::class,
        "1C292/4" => Exceptions\IpboardMemberUsernameExists::class,
        "1C292/5" => Exceptions\IpboardMemberEmailExists::class,
        "1C292/6" => Exceptions\IpboardMemberInvalidGroup::class,
        "1C292/7" => Exceptions\IpboardMemberIDInvalid::class,

        // forums/posts
        "1F295/4" => Exceptions\IpboardForumPostIdInvalid::class,
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
     * Call core/hello to find details of forum instance.
     *
     * @return string json return.
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     */
    public function hello()
    {
        return $this->getRequest("core/hello");
    }

    /**
     * Call to core/members to get a specific page of users.
     *
     * @param string $sortBy  Possible values are joined, name or ID (Default ID)
     * @param string $sortDir Possible values are 'asc' and 'desc' (Default asc)
     * @param int    $page    Any positive integer, up to the maximum number of pages.
     *
     * @return string json return.
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     */
    public function getMembersByPage($sortBy = "ID", $sortDir = "asc", $page = 1)
    {
        return $this->getRequest("core/members",
            ["query" => ["sortBy" => $sortBy, "sortDir" => $sortDir, "page" => $page]]);
    }

    /**
     * Call to core/members to get all users in the database.
     *
     * @param string $sortBy  Possible values are joined, name or ID (Default ID)
     * @param string $sortDir Possible values are 'asc' and 'desc' (Default asc)
     *
     * @return string json return.
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     */
    public function getMembersAll($sortBy = "ID", $sortDir = "asc")
    {
        $allMembers = [];

        $currentPage = 1;
        do {
            $response = $this->getMembersByPage($sortBy, $sortDir, $currentPage);
            $allMembers = array_merge($allMembers, $response->results);
            $currentPage++;
        } while ($currentPage <= $response->totalPages);

        return $allMembers;
    }

    /**
     * Get a specific member details by their ID number.
     *
     * @param $memberID The ID number of the member to retrieve details for.
     *
     * @return string
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function getMemberById($memberID)
    {
        return $this->getRequest("core/members/" . $memberID);
    }

    /**
     * Create a new member with the given information.
     *
     * @param $name  The display/username of the member to create.
     * @param $email The email address to associate with the member.
     * @param $password The password to create the user account with.
     * @param $group The primary group to assign to the member (default = null, members)
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
     */
    public function createMember($name, $email, $password, $group = null)
    {
        return $this->postRequest("core/members", compact("name", "email", "password", "group"));
    }

    /**
     * Update an existing member with the details provided.
     *
     * @param integer $memberID  The member ID of the member to update.
     * @param array $data Array of data (Allowed keys are name, email and password).
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
     */
    public function updateMember($memberID, array $data=[])
    {
        return $this->postRequest("core/members/" . $memberID, $data);
    }

    /**
     * Delete a member with the given ID.
     *
     * @param integer $memberID  The member ID of the member to delete.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function deleteMemberById($memberID){
        return $this->deleteRequest("core/members/".$memberID);
    }

    public function getForumPosts($searchCriteria){
        $this->validateForumPostsSearchCriteria($searchCriteria);
    }

    private function validateForumPostsSearchCriteria($data){
        if(is_comma_separated(array_get($data, "forums"))){
            throw new Exceptions\InvalidFormat("Forums IDs must be separated by a comma.");
        }
    }

    private function sanitizeForumPostsSearchCriteria($data){
    }

    /**
     * Get a specific forum post given the ID.
     *
     * @param $postId The ID of the forum post to retrieve.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function getForumPostById($postId){
        return $this->getRequest("forums/posts/".$postId);
    }

    /**
     * Perform a get request.
     *
     * @param       $function The endpoint to call via GET.
     * @param array $extra    Any query string parameters.
     *
     * @return string json return.
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    private function getRequest($function, $extra = [])
    {
        return $this->request("GET", $function, $extra);
    }

    /**
     * Perform a post request.
     *
     * @param $function The endpoint to perform a POST request on.
     * @param $data     The form data to be sent.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
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
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function deleteRequest($function){
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
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
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
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
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

