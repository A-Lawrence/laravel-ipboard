<?php

namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey;
use Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardThrottled;

trait Forums
{
    /**
     * Fetch all forums
     *
     * @return mixed
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     */
    public function getForumsAll()
    {
        return $this->getRequest("forums/forums");
    }

    /**
     * Get a specific forum given the ID.
     *
     * @param integer $forumId The ID of the forum post to retrieve.
     *
     * @return mixed
     * @throws Exceptions\IpboardMemberEmailExists
     * @throws Exceptions\IpboardMemberInvalidGroup
     * @throws Exceptions\IpboardMemberUsernameExists
     * @throws IpboardInvalidApiKey
     * @throws IpboardMemberIdInvalid
     * @throws IpboardThrottled
     * @throws \Exception
     */
    public function getForumById($forumId)
    {
        return $this->getRequest("forums/forums/" . $forumId);
    }
}