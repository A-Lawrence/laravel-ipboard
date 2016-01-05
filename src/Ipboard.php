<?php namespace Alawrence\Ipboard;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Mockery\CountValidator\Exception;

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
     * @param $name     The display/username of the member to create.
     * @param $email    The email address to associate with the member.
     * @param $password The password to create the user account with.
     * @param $group    The primary group to assign to the member (default = null, members)
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
     * @param integer $memberID The member ID of the member to update.
     * @param array   $data     Array of data (Allowed keys are name, email and password).
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws Exceptions\IpboardMemberEmailExists
     */
    public function updateMember($memberID, array $data = [])
    {
        return $this->postRequest("core/members/" . $memberID, $data);
    }

    /**
     * Delete a member with the given ID.
     *
     * @param integer $memberID The member ID of the member to delete.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function deleteMemberById($memberID)
    {
        return $this->deleteRequest("core/members/" . $memberID);
    }

    /**
     * Fetch all forum posts that match the given search criteria
     *
     * @param $searchCriteria The search criteria posts should match.
     * @param $page           The page number to retrieve (default 1).
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     */
    public function getForumPostsByPage($searchCriteria, $page = 1)
    {
        $validator = \Validator::make($searchCriteria, [
            "forums"        => "string|is_csv_numeric",
            "authors"       => "string|is_csv_numeric",
            "hasBestAnswer" => "in:1,0",
            "hasPoll"       => "in:1,0",
            "locked"        => "in:1,0",
            "hidden"        => "in:1,0",
            "pinned"        => "in:1,0",
            "featured"      => "in:1,0",
            "archived"      => "in:1,0",
            "sortBy"        => "in:id,date,title",
            "sortDir"       => "in:asc,desc",
        ], [
            "is_csv_numeric" => "The :attribute must be a comma separated string of IDs.",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->getRequest("forums/posts", array_merge($searchCriteria, ["page" => $page]));
    }

    /**
     * Fetch all forum posts that match the given search criteria
     *
     * @param $searchCriteria The search criteria posts should match.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     */
    public function getForumPostsAll($searchCriteria)
    {
        $allPosts = [];

        $currentPage = 1;
        do {
            $response = $this->getForumPostsByPage($searchCriteria, $currentPage);
            $allPosts = array_merge($allPosts, $response->results);
            $currentPage++;
        } while ($currentPage <= $response->totalPages);

        return $allPosts;
    }

    /**
     * Get a specific forum post given the ID.
     *
     * @param $postId The ID of the forum post to retrieve.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardForumPostIdInvalid
     */
    public function getForumPostById($postId)
    {
        return $this->getRequest("forums/posts/" . $postId);
    }

    /**
     * Create a forum post with the given data.
     *
     * @param integer $topicID  The ID of the topic to add the post to.
     * @param integer $authorID The ID of the author for the post (if set to 0, author_name is used)
     * @param stromg  $post     The HTML content of the post.
     * @param array   $extra
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardForumTopicIdInvalid
     * @throws Exceptions\IpboardPostInvalid
     */
    public function createForumPost($topicID, $authorID, $post, $extra = [])
    {
        $data = ["topic" => $topicID, "author" => $authorID, "post" => $post];
        $data = array_merge($data, $extra);

        $validator = \Validator::make($data, [
            "topic"       => "required|numeric",
            "author"      => "required|numeric",
            "post"        => "required|string",
            "author_name" => "required_if:author,0|string",
            "date"        => "date_format:YYYY-mm-dd H:i:s",
            "ip_address"  => "ip",
            "hidden"      => "in:-1,0,1",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest("forums/posts", $data);
    }

    /**
     * Update a forum post with the given ID.
     *
     * @param integer $postID The ID of the post to update.
     * @param array   $data   The data to edit.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardForumPostIdInvalid
     * @throws Exceptions\IpboardPostInvalid
     * @throws Exceptions\IpboardCannotHideFirstPost
     * @throws Exceptions\IpboardCannotAuthorFirstPost
     */
    public function updateForumPost($postId, $data = [])
    {
        $validator = \Validator::make($data, [
            "author"      => "numeric",
            "author_name" => "required_if:author,0|string",
            "post"        => "string",
            "hidden"      => "in:-1,0,1",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest("forums/posts/" . $postId, $data);
    }

    /**
     * Delete a forum post given it's ID.
     *
     * @param $postId The ID of the post to delete.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardForumPostIdInvalid
     * @throws Exceptions\IpboardCannotDeleteFirstPost
     */
    public function deleteForumPost($postId)
    {
        return $this->deleteRequest("forums/posts/" . $postId);
    }

    /**
     * Fetch all forum topics that match the given search criteria
     *
     * @param $searchCriteria The search criteria topics should match.
     * @param $page           The page number to retrieve (default 1).
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     */
    public function getForumTopicsByPage($searchCriteria, $page = 1)
    {
        $validator = \Validator::make($searchCriteria, [
            "forums"        => "string|is_csv_numeric",
            "authors"       => "string|is_csv_numeric",
            "hasBestAnswer" => "in:1,0",
            "hasPoll"       => "in:1,0",
            "locked"        => "in:1,0",
            "hidden"        => "in:1,0",
            "pinned"        => "in:1,0",
            "featured"      => "in:1,0",
            "archived"      => "in:1,0",
            "sortBy"        => "in:id,date,title",
            "sortDir"       => "in:asc,desc",
        ], [
            "is_csv_numeric" => "The :attribute must be a comma separated string of IDs.",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->getRequest("forums/topics", array_merge($searchCriteria, ["page" => $page]));
    }

    /**
     * Fetch all forum topics that match the given search criteria
     *
     * @param $searchCriteria The search criteria topics should match.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     */
    public function getForumTopicsAll($searchCriteria)
    {
        $allTopics = [];

        $currentPage = 1;
        do {
            $response = $this->getForumTopicsByPage($searchCriteria, $currentPage);
            $allTopics = array_merge($allTopics, $response->results);
            $currentPage++;
        } while ($currentPage <= $response->totalPages);

        return $allTopics;
    }

    /**
     * Get a specific forum topic given the ID.
     *
     * @param $postId The ID of the forum topic to retrieve.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardForumTopicIdInvalid
     */
    public function getForumTopicById($topicId)
    {
        return $this->getRequest("forums/topics/" . $topicId);
    }

    /**
     * Create a forum topic with the given data.
     *
     * @param integer forumID  The ID of the forum to add the topic to.
     * @param integer $authorID The ID of the author for the topic (if set to 0, author_name is used)
     * @param string  $title    The title of the topic.
     * @param string  $post     The HTML content of the post.
     * @param array   $extra
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws Exceptions\IpboardForumIdInvalid
     * @throws Exceptions\IpboardMemberIdInvalid
     * @throws Exceptions\IpboardTopicTitleInvalid
     * @throws Exceptions\IpboardPostInvalid
     */
    public function createForumTopic($forumID, $authorID, $title, $post, $extra = [])
    {
        $data = ["forum" => $forumID, "author" => $authorID, "title" => $title, "post" => $post];
        $data = array_merge($data, $extra);

        $validator = \Validator::make($data, [
            "forum"       => "required|numeric",
            "author"      => "required|numeric",
            "title"       => "required|string",
            "post"        => "required|string",
            "author_name" => "required_if:author,0|string",
            "prefix"      => "string",
            "tags"        => "string|is_csv_alphanumeric",
            "date"        => "date_format:YYYY-mm-dd H:i:s",
            "ip_address"  => "ip",
            "locked"      => "in:0,1",
            "open_time"   => "date_format:YYYY-mm-dd H:i:s",
            "close_time"  => "date_format:YYYY-mm-dd H:i:s",
            "hidden"      => "in:-1,0,1",
            "pinned"      => "in:0,1",
            "featured"    => "in:0,1",
        ], [
            "is_csv_alphanumeric" => "The :attribute must be a comma separated string.",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest("forums/topics", $data);
    }

    /**
     * Update a forum topic with the given ID.
     *
     * @param integer $topicID The ID of the topic to update.
     * @param array   $data   The data to edit.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws Exceptions\IpboardForumTopicIdInvalid
     * @throws Exceptions\IpboardForumIdInvalid
     * @throws Exceptions\IpboardMemberIdInvalid
     */
    public function updateForumTopic($topicID, $data = [])
    {
        $validator = \Validator::make($data, [
            "forum" => "numeric",
            "author"      => "numeric",
            "author_name" => "required_if:author,0|string",
            "title" => "string",
            "post" => "string",
            "prefix" => "string",
            "tags"        => "string|is_csv_alphanumeric",
            "date"        => "date_format:YYYY-mm-dd H:i:s",
            "ip_address"  => "ip",
            "locked"      => "in:0,1",
            "open_time"   => "date_format:YYYY-mm-dd H:i:s",
            "close_time"  => "date_format:YYYY-mm-dd H:i:s",
            "hidden"      => "in:-1,0,1",
            "pinned"      => "in:0,1",
            "featured"    => "in:0,1",
        ], [
            "is_csv_alphanumeric" => "The :attribute must be a comma separated string.",
        ]);

        if ($validator->fails()) {
            $message = head(array_flatten($validator->messages()));
            throw new Exceptions\InvalidFormat($message);
        }

        return $this->postRequest("forums/topics/" . $topicID, $data);
    }

    /**
     * Delete a forum topic given it's ID.
     *
     * @param $topicId The ID of the topic to delete.
     *
     * @return mixed
     * @throws Exceptions\IpboardInvalidApiKey
     * @throws Exceptions\IpboardThrottled
     * @throws Exceptions\IpboardForumPostIdInvalid
     * @throws Exceptions\IpboardCannotDeleteFirstPost
     */
    public function deleteForumTopic($topicId)
    {
        return $this->deleteRequest("forums/topics/" . $topicId);
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
    public function deleteRequest($function)
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

