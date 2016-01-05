<?php namespace Alawrence\Ipboard\Console;

use Alawrence\Ipboard\Ipboard;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $name = 'Ipboard:test';
    protected $description = 'Make a test call using the given API settings.';
    protected $ipboard;

    public function __construct(Ipboard $ipboard)
    {
        parent::__construct();

        $this->ipboard = $ipboard;
    }

    public function fire()
    {
        $members = $this->ipboard->getMembersByPage();

        exit();
        $result = $this->ipboard->hello();

        if(array_get($result, "communityName", null)){
            $this->info("Your community is online:");
            $this->info("URL: ".$result["communityUrl"]);
            $this->info("Name: ".$result["communityName"]);
            $this->info("Version: ".$result["ipsVersion"]);
            return;
        }

        $this->error("It doesn't look like your community API can be reached.  Check your config file (config/ipboard.php)");
        $this->error("If you cannot find your config file, ensure you have published all vendor files.");
    }
}