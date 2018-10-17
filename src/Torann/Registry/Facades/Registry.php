<?php namespace Torann\Registry\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * The facade for @see \Torann\Registry\Registry
 * @property $cache
 *
 * @method static get($key, $default = null)
 * @method static set($key, $value)
 * @method static overwrite($key, $value)
 * @method static store(array $values)
 * @method static forget($key)
 * @method static flush()
 * @method static all($default = array())
 */
class Registry extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'registry'; }

}
