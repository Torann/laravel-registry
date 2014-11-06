<?php namespace Torann\Registry;

use Exception;

use Illuminate\Database\DatabaseManager;
use Illuminate\Cache\CacheManager;

class Registry {

    /**
     * Registry cache
     *
     * @var object
     */
    protected $cache_storage = null;

    /**
     * Registry table name
     *
     * @var string
     */
    protected $table;

    /**
     * Database manager instance
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $database;

    /**
     * Cache instance
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param \Illuminate\Database\DatabaseManager $database
     * @param \Illuminate\Cache\CacheManager       $cache
     */
    public function __construct(DatabaseManager $database, CacheManager $cache, $config = array())
    {
        $this->database = $database;
        $this->cache    = $cache;
        $this->table    = $config['table'];

        // Ensure cache is set
        $this->setCache();
    }

    /**
     * Get value from registry
     *
     * @param  string  $key
     * @param  string  $default
     * @param  bool    $forceType
     * @return mixed
     */
    public function get($key, $default = null)
    {
        list($baseKey, $searchKey) = $this->fetchKey($key);

        $value = $this->fetchValue($baseKey, $searchKey);

        return (! is_null($value)) ? $value : $default;
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

            $this->database->table($this->table)->insert(array('key' => $baseKey, 'value' => json_encode($object)));

            $this->cache_storage[$baseKey] = $object;
        }
        else
        {
            $this->database->table($this->table)->insert(array('key' => $baseKey, 'value' => json_encode($value)));

            $this->cache_storage[$baseKey] = $value;
        }

        $this->cache->forever('torann.registry', $this->cache_storage);

        return true;
    }

    /**
     * Overwrite existing value from registry
     *
     * @param  string $key
     * @param  mixed $value
     * @throws Exception
     * @return bool
     */
    public function overwrite($key, $value)
    {
        list($baseKey, $searchKey) = $this->fetchKey($key);
        $registry = $this->get($baseKey);

        if (is_null($registry)) throw new Exception("Item [$key] does not exists");

        if ($baseKey !=  $searchKey)
        {
            array_set($registry, $searchKey, $value);
            $this->database->table($this->table)->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

            $this->cache_storage[$baseKey] = $registry;
        }
        else
        {
            $this->database->table($this->table)->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));

            $this->cache_storage[$baseKey] = $value;
        }

        $this->cache->forever('torann.registry', $this->cache_storage);

        return true;
    }

    /**
     * Store an array
     *
     * @param  array $values
     * @return bool
     */
    public function store(array $values)
    {
        foreach ($values as $key=>$value)
        {
            // Ensure proper type
            $value = $this->forceTypes($value);

            // Json to save
            $jsonValue = json_encode($value);

            // Update
            $this->database->statement("INSERT INTO system_registries ( `key`, `value` ) VALUES ( ?, ? )
										ON DUPLICATE KEY UPDATE `key` = ?, `value` = ?",
                array($key, $jsonValue, $key, $jsonValue));

            $this->cache_storage[$key] = $value;
        }

        $this->cache->forever('torann.registry', $this->cache_storage);

        return true;
    }

    /**
     * Remove existing value from registry
     *
     * @param  string $key
     * @throws Exception
     * @return bool
     */
    public function forget($key)
    {
        list($baseKey, $searchKey) = $this->fetchKey($key);
        $registry = $this->get($baseKey);

        if (is_null($registry)) throw new Exception("Item [$key] does not exists");

        if ($baseKey !== $searchKey)
        {
            array_forget($registry, $searchKey);
            $this->database->table($this->table)->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

            $this->cache_storage[$baseKey] = $registry;
        }
        else
        {
            $this->database->table($this->table)->where('key', '=', $baseKey)->delete();

            unset($this->cache_storage[$baseKey]);
        }

        $this->cache->forever('torann.registry', $this->cache_storage);

        return true;
    }

    /**
     * Clear registry
     *
     * @return bool
     */
    public function flush()
    {
        $this->cache->forget('torann.registry');

        $this->cache_storage = null;

        return $this->database->table($this->table)->truncate();
    }

    /**
     * Fetch all values
     *
     * @param  mixed $default
     * @return mixed
     */
    public function all($default = array())
    {
        return ( ! empty($this->cache_storage) ) ? $this->cache_storage : $default;
    }

    /**
     * Cast values to native PHP variable types.
     *
     * @param  mixed  $data
     * @return mixed
     */
    protected function forceTypes($data)
    {
        if (in_array($data, array('true', 'false')))
        {
            $data = (bool) $data;
        }
        else if (is_numeric($data))
        {
            $data = (int) $data;
        }
        else if (gettype($data) === 'array')
        {
            foreach($data as $key=>$value)
            {
                $data[$key] = $this->forceTypes($value);
            }
        }

        return $data;
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
        $db    = $this->database;
        $table = $this->table;

        $this->cache_storage = $this->cache->rememberForever("torann.registry", function() use ($db, $table)
        {
            $cache = array();

            foreach($db->table($table)->get() as $setting)
            {
                $cache[$setting->key] = json_decode($setting->value, true);
            }

            return $cache;
        });
    }
}
