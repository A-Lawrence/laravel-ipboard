<?php

namespace Alawrence\Ipboard;

trait Hello
{
    /**
     * Call core/hello to find details of forum instance.
     *
     * @return string json return.
     *
     * @throws \Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey
     * @throws \Alawrence\Ipboard\Exceptions\IpboardThrottled
     * @throws \Alawrence\Ipboard\Exceptions\IpboardMemberIdInvalid
     */
    public function hello()
    {
        return $this->getRequest("core/hello");
    }
}