<?php namespace Alawrence\IPBoardApi\Facades;

use Illuminate\Support\Facades\Facade;

class Ipboard extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'ipboard'; }
}