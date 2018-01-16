<?php namespace Torann\Registry\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\DatabaseManager;
use Torann\Registry\Cache;

/**
 * The facade for @see \Torann\Registry\Registry
 * @property $cache
 *
 * @method __construct(DatabaseManager $database, Cache $cache, $config = array())
 * @method get($key, $default = null)
 * @method set($key, $value)
 * @method overwrite($key, $value)
 * @method store(array $values)
 * @method forget($key)
 * @method flush()
 * @method all($default = array())
 */
class Registry extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'registry'; }

}
