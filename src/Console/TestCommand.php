<?php namespace Alawrence\Ipboard\Console;

use Alawrence\Ipboard\Ipboard;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $name = 'ipboard:test';
    protected $description = 'Make a test call using the given API settings.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        IPBoardAPI::basic();
    }
}