<?php namespace Alawrence\Ipboard;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

class Ipboard
{
    protected $url;
    protected $key;
    protected $reference;
    protected $httpRequest;
    private $error_exceptions = [
        520 => \Exception::class,
        401 => Exceptions\IpboardUnauthorized::class,
        429 => Exceptions\IpBoardThrottled::class,
    ];

    public function __construct()
    {
        $this->url = config("ipboard.api_url");
        $this->key = config("ipboard.api_key");
        $this->reference = config("ipboard.api_reference_name");

        $this->httpRequest = new HttpClient([
            "base_url" => $this->url,
            "timeout"  => 2.0,
            "defaults" => [
                "auth" => [$this->key, ""],
            ]
        ]);
    }

    public function hello()
    {
        return $this->getRequest("core/hello")->json();
    }

    public function getMembersByPage($sortBy="ID", $sortDir="asc", $page=1){
        return $this->getRequest("core/members", ["query" => ["sortBy" => $sortBy, "sortDir" => $sortDir, "page" => $page]])->json();
    }

    public function getMembersAll($sortBy="ID", $sortDir="asc"){

    }

    private function request($method, $function)
    {
        $response = null;
        try {
            $response = $this->httpRequest->{$method}($function);
            return $response;
        } catch(ClientException $e){
            $this->handleError($e->getCode());
        }
    }

    private function getRequest($function){
        return $this->request("get", $function);
    }

    private function handleError($code){
        try {
            throw new $this->error_exceptions[$code];
        } catch(Exception $e){
            throw new \Exception("There was a malformed response from IPBoard.");
        }
    }

}

