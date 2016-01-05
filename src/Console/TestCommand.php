<?php namespace Alawrence\IPBoardAPI\Console;

use Alawrence\IPBoardAPI\IPBoardAPI;
use Illuminate\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'ipboardapi:test';
    protected $description = 'Make a test call using the given API settings.';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        //TODO: Make some test command here.
    }
}