<?php namespace Alawrence\IPBoardAPI;

use GuzzleHttp\Client as HttpClient;

class IPBoardAPI
{
    protected $url;
    protected $key;
    protected $reference;

    public function __construct()
    {
        $this->url = config("ipboardapi.api_url");
        $this->key = config("ipboardapi.api_key");
        $this->reference = config("ipboardapi.api_reference_name");
    }

    public function make(){

    }

    public function basic(){
        $apiRequest = new HttpClient();
        $result = $apiRequest->request("GET", $this->url."core/hello", ["auth" => [$this->key, ""]]);

        dd($result);
    }

}

