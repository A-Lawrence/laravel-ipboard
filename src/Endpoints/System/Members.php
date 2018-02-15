<?php

namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey;
use Alawrence\Ipboard\Exceptions\IpboardMemberEmailExists;
use Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid;
use Alawrence\Ipboard\Exceptions\IpboardMemberInvalidGroup;
use Alawrence\Ipboard\Exceptions\IpboardMemberUsernameExists;
use Alawrence\Ipboard\Exceptions\IpboardThrottled;

trait Members
{
    /**
     * Call to core/members to get a specific page of users.
     *
     * @param string $sortBy  Possible values are joined, name or ID (Default ID)
     * @param string $sortDir Possible values are 'asc' and 'desc' (Default asc)
     * @param integer   $page    Any positive integer, up to the maximum number of pages.
     *
     * @return string json return.
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberInvalidGroup
     * @throws IpboardMemberUsernameExists
     * @throws IpboardMemberEmailExists
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     * @throws IpboardMemberInvalidGroup
     * @throws IpboardMemberUsernameExists
     * @throws IpboardMemberEmailExists
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
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     * @throws IpboardMemberIdInvalid
     */
    public function deleteMemberById($memberID)
    {
        return $this->deleteRequest("core/members/" . $memberID);
    }
}