<?php

namespace Alawrence\Ipboard;

use Alawrence\Ipboard\Exceptions\IpboardInvalidApiKey;
use Alawrence\Ipboard\Exceptions\IpboardThrottled;

trait Hello
{
    /**
     * Call core/hello to find details of forum instance.
     *
     * @return string json return.
     * @throws IpboardInvalidApiKey
     * @throws IpboardThrottled
     */
    public function hello()
    {
        return $this->getRequest("core/hello");
    }
}