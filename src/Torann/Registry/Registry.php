<?php namespace Torann\Registry;

use Cache;

class Registry {

	/**
	 * Registry cache
	 *
	 * @var object
	 */
	protected $cache = null;

	/**
	 * Application instance
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Registry table name
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Constructor
	 *
	 * @param Illuminate\Foundation\Application $app
	 */
	public function __construct($app)
	{
		$this->app = $app;

		// Ensure cache is set
		$this->setCache();
	}

	/**
	 * Get value from registry
	 *
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);

		$value = $this->fetchValue($baseKey, $searchKey);

		return ( ! is_null($value) ) ? $value : $default;
	}

	/**
	 * Store value into registry
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return bool
	 */
	public function set($key, $value)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if ( ! is_null($registry)) return $this->overwrite($key, $value);

		if ($baseKey != $searchKey)
		{
			$object = array();
			$level = '';
			$keys = explode('.', $searchKey);

			foreach ($keys as $key)
			{
				$level .= '.'.$key;
				(trim($level, '.') == $searchKey) ? array_set($object, trim($level, '.'), $value) : array_set($object, trim($level, '.'), array());
			}

			$this->app['db']->table('system_registries')->insert(array('key' => $baseKey, 'value' => json_encode($object)));

			$this->cache[$baseKey] = $object;
		}
		else
		{
			$this->app['db']->table('system_registries')->insert(array('key' => $baseKey, 'value' => json_encode($value)));

			$this->cache[$baseKey] = $value;
		}

		Cache::forever('torann.registry', $this->cache);

		return true;
	}

	 /**
	 * Overwrite existing value from registry
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @throw Exception
	 * @return bool
	 */
	public function overwrite($key, $value)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if (is_null($registry)) throw new \Exception("Item [$key] does not exists");

		if ($baseKey !=  $searchKey)
		{
			array_set($registry, $searchKey, $value);
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

			$this->cache[$baseKey] = $registry;
		}
		else
		{
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));

			$this->cache[$baseKey] = $value;
		}

		Cache::forever('torann.registry', $this->cache);

		return true;
	}

	 /**
	 * Store an array
	 *
	 * @param  srray $key
	 * @return bool
	 */
	public function store(array $values)
	{
		// Ensure cache is set
		$this->setCache();

		foreach ($values as $key=>$value)
		{
			$jsonValue = json_encode($value);
			$this->app['db']->statement("INSERT INTO system_registries ( `key`, `value` ) VALUES ( ?, ? )
										ON DUPLICATE KEY UPDATE `key` = ?, `value` = ?",
										array($key, $jsonValue, $key, $jsonValue));

			$this->cache[$key] = $value;
		}

		Cache::forever('torann.registry', $this->cache);

		return true;
	}

	/**
	 * Remove existing value from registry
	 *
	 * @param  string $key
	 * @throw Exception
	 * @return bool
	 */
	public function forget($key)
	{
		list($baseKey, $searchKey) = $this->fetchKey($key);
		$registry = $this->get($baseKey);

		if (is_null($registry)) throw new \Exception("Item [$key] does not exists");

		if ($baseKey !== $searchKey)
		{
			array_forget($registry, $searchKey);
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

			$this->cache[$baseKey] = $registry;
		}
		else
		{
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->delete();

			unset($this->cache[$baseKey]);
		}

		Cache::forever('torann.registry', $this->cache);

		return true;
	}

	/**
	 * Clear registry
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function flush()
	{
		Cache::forget('torann.registry');

		$this->cache = null;

		return $this->app['db']->table('system_registries')->truncate();
	}

	/**
	 * Fetch all values
	 *
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	public function all($default = null)
	{
		// Ensure cache is set
		$this->setCache();

		return ( ! empty($this->cache) ) ? $this->cache : $default;
	}

	/**
	 * Get registry key
	 *
	 * @param  string $key
	 * @return array
	 */
	protected function fetchKey($key)
	{
		if (str_contains($key, '.'))
		{
			$keys = explode('.', $key);
			$search = array_except($keys, 0);

			return array(array_get($keys, 0), implode('.', $search));
		}

		return array($key, $key);
	}

	/**
	 * Get key value
	 *
	 * @param  string $key
	 * @param  string $searchKey
	 * @return mixed
	 */
	protected function fetchValue($key, $searchKey = null)
	{
		// Ensure cache is set
		$this->setCache();

		if ( ! isset($this->cache[$key]) ) return null;

		$object = $this->cache[$key];

		return ! is_null($searchKey) ? array_get($object, $searchKey, $object) : array_get($object, $key, $object);
	}

	/**
	 * Set cache
	 *
	 * @return array
	 */
	protected function setCache()
	{
		if( $this->cache === null )
		{
			$this->cache = Cache::rememberForever("torann.registry", function()
			{
				$cache = array();
				foreach($this->app['db']->table('system_registries')->get() as $setting)
				{
					$cache[$setting->key] = json_decode($setting->value, true);
				}

				return $cache;
			});
		}
	}

}
