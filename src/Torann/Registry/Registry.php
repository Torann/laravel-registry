<?php namespace Torann\Registry;

use Cache;

class Registry {

	/**
	 * Registry cache
	 *
	 * @var object
	 */
	protected $cache_storage = null;

	/**
	 * Application instance
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @param \Illuminate\Foundation\Application $app
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

			$this->cache_storage[$baseKey] = $object;
		}
		else
		{
			$this->app['db']->table('system_registries')->insert(array('key' => $baseKey, 'value' => json_encode($value)));

			$this->cache_storage[$baseKey] = $value;
		}

		Cache::forever('torann.registry', $this->cache_storage);

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

			$this->cache_storage[$baseKey] = $registry;
		}
		else
		{
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));

			$this->cache_storage[$baseKey] = $value;
		}

		Cache::forever('torann.registry', $this->cache_storage);

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
		foreach ($values as $key=>$value)
		{
			$jsonValue = json_encode($value);
			$this->app['db']->statement("INSERT INTO system_registries ( `key`, `value` ) VALUES ( ?, ? )
										ON DUPLICATE KEY UPDATE `key` = ?, `value` = ?",
										array($key, $jsonValue, $key, $jsonValue));

			$this->cache_storage[$key] = $value;
		}

		Cache::forever('torann.registry', $this->cache_storage);

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

			$this->cache_storage[$baseKey] = $registry;
		}
		else
		{
			$this->app['db']->table('system_registries')->where('key', '=', $baseKey)->delete();

			unset($this->cache_storage[$baseKey]);
		}

		Cache::forever('torann.registry', $this->cache_storage);

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

		$this->cache_storage = null;

		return $this->app['db']->table('system_registries')->truncate();
	}

	/**
	 * Fetch all values
	 *
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	public function all($default = array())
	{
		return ( ! empty($this->cache_storage) ) ? $this->cache_storage : $default;
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
		if ( ! isset($this->cache_storage[$key]) ) return null;

		$object = $this->cache_storage[$key];

		return ! is_null($searchKey) ? array_get($object, $searchKey, $object) : array_get($object, $key, $object);
	}

	/**
	 * Set cache
	 *
	 * @return array
	 */
	protected function setCache()
	{
		$db = $this->app['db'];
		
		$this->cache_storage = Cache::rememberForever("torann.registry", function() use ($db)
		{
			$cache = array();
			foreach($db->table('system_registries')->get() as $setting)
			{
				$cache[$setting->key] = json_decode($setting->value, true);
			}
			return $cache;
		});
	}

}
