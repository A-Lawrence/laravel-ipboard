<?php namespace Alawrence\Ipboard;

use GuzzleHttp\Client as HttpClient;

class Ipboard
{
    protected $url;
    protected $key;
    protected $reference;

    public function __construct()
    {
        $this->url = config("ipboard.api_url");
        $this->key = config("ipboard.api_key");
        $this->reference = config("ipboard.api_reference_name");
    }

    public function make(){

    }

    public function basic(){
        $apiRequest = new HttpClient();
        $result = $apiRequest->request("GET", $this->url."core/hello", ["auth" => [$this->key, ""]]);

        dd($result);
    }

}

