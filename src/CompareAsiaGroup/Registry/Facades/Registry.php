<?php namespace CompareAsiaGroup\Registry\Facades;

use Illuminate\Support\Facades\Facade;

class Registry extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'registry'; }

}
