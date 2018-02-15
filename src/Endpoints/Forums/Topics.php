<?php

namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Exceptions\InvalidFormat;
use Alawrence\Ipboard\Exceptions\IpboardCannotDeleteFirstPost;
use Alawrence\Ipboard\Exceptions\IpboardForumIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardForumPostIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardForumTopicIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey;
use Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardPostInvalid;
use Alawrence\Ipboard\Exceptions\IpboardThrottled;
use Alawrence\Ipboard\Exceptions\IpboardTopicTitleInvalid;

trait Topics
{
    /**
     * Fetch all forum topics that match the given search criteria
     *
     * @param array $searchCriteria The search criteria topics should match.
     * @param int   $page           The page number to retrieve (default 1).
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
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
            throw new InvalidFormat($message);
        }

        return $this->getRequest("forums/topics", array_merge($searchCriteria, ["page" => $page]));
    }

    /**
     * Fetch all forum topics that match the given search criteria
     *
     * @param int $searchCriteria The search criteria topics should match.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
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
     * @param int $topicId The ID of the forum topic to retrieve.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardForumTopicIdInvalid
     */
    public function getForumTopicById($topicId)
    {
        return $this->getRequest("forums/topics/" . $topicId);
    }

    /**
     * Get a specific forum topic given the ID.
     *
     * @param int $topicId The ID of the forum topic to retrieve.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardForumTopicIdInvalid
     */
    public function getForumTopicPosts($topicId)
    {
        return $this->getRequest("forums/topics/" . $topicId . "/posts");
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws IpboardForumIdInvalid
     * @throws IpboardMemberIdInvalid
     * @throws IpboardTopicTitleInvalid
     * @throws IpboardPostInvalid
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
            throw new InvalidFormat($message);
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\InvalidFormat
     * @throws IpboardForumTopicIdInvalid
     * @throws IpboardForumIdInvalid
     * @throws IpboardMemberIdInvalid
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
            throw new InvalidFormat($message);
        }

        return $this->postRequest("forums/topics/" . $topicID, $data);
    }

    /**
     * Delete a forum topic given it's ID.
     *
     * @param $topicId The ID of the topic to delete.
     *
     * @return mixed
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardForumPostIdInvalid
     * @throws IpboardCannotDeleteFirstPost
     */
    public function deleteForumTopic($topicId)
    {
        return $this->deleteRequest("forums/topics/" . $topicId);
    }
}