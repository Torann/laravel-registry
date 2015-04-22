<?php namespace CompareAsiaGroup\Registry;

use Exception;
use Illuminate\Database\DatabaseManager;

class Registry {

    /**
     * Registry config
     *
     * @var array
     */
    protected $config;

    /**
     * Application instance
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Database manager instance
     *
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $database;

    /**
     * Cache instance
     *
     * @var Cache
     */
    public $cache;

    /**
     * Constructor
     *
     * @param DatabaseManager $database
     * @param Cache           $cache
     */
    public function __construct(DatabaseManager $database, Cache $cache, $config = array())
    {
        $this->database = $database;
        $this->config   = $config;
        $this->cache    = $cache;

        // Ensure cache is set
        $this->setCache();
    }

    /**
     * Get value from registry
     *
     * @param  string  $key
     * @param  string  $default
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

        if ($registry === null) {
            $registry = $this->cache->get($baseKey);
        }

        if (! is_null($registry)) {
            return $this->overwrite($key, $value);
        }

        if ($baseKey != $searchKey)
        {
            $object = array();
            $level  = '';
            $keys   = explode('.', $searchKey);

            foreach ($keys as $key)
            {
                $level .= '.'.$key;
                (trim($level, '.') == $searchKey) ? array_set($object, trim($level, '.'), $value) : array_set($object, trim($level, '.'), array());
            }

            $this->database->table($this->config['table'])->insert(array('key' => $baseKey, 'value' => json_encode($object)));

            // Add to cache
            $this->cache->add($baseKey, $object);
        }
        else
        {
            $this->database->table($this->config['table'])->insert(array('key' => $baseKey, 'value' => json_encode($value)));

            // Add to cache
            $this->cache->add($baseKey, $value);
        }

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

        if ($registry === null) {
            $registry = $this->cache->get($baseKey);
        }

        if (is_null($registry)) {
            throw new Exception("Item [$key] does not exists");
        }

        if ($baseKey !=  $searchKey)
        {
            array_set($registry, $searchKey, $value);
            $this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

            $this->cache->add($baseKey, $registry);
        }
        else
        {
            $this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($value)));

            $this->cache->add($baseKey, $value);
        }

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

            $this->cache->add($key, $value);
        }

        return true;
    }

    /**
     * Remove existing value from registry
     *
     * @param  string $key
     * @throws Exception
     * @return bool
     * @throws Exception
     */
    public function forget($key)
    {
        list($baseKey, $searchKey) = $this->fetchKey($key);

        $registry = $this->get($baseKey);

        if ($registry === null) {
            $registry = $this->cache->get($baseKey);
        }

        if (is_null($registry)) {
            throw new Exception("Item [$key] does not exists");
        }

        if ($baseKey !== $searchKey)
        {
            array_forget($registry, $searchKey);
            $this->database->table($this->config['table'])->where('key', '=', $baseKey)->update(array('value' => json_encode($registry)));

            // Update cache
            $this->cache->add($baseKey, $registry);
        }
        else
        {
            $this->database->table($this->config['table'])->where('key', '=', $baseKey)->delete();

            // Remove from cache
            $this->cache->remove($baseKey);
        }

        return true;
    }

    /**
     * Clear registry
     *
     * @return bool
     */
    public function flush()
    {
        $this->cache->flush();

        return $this->database->table($this->config['table'])->truncate();
    }

    /**
     * Fetch all values
     *
     * @param  mixed $default
     * @return mixed
     */
    public function all($default = array())
    {
        return $this->cache->all($default);
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
            $data = ($data === 'true' ? 1 : 0);
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

        return array($key, null);
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
        $object = $this->cache->get($key);

        if (is_null($object)) {
            return null;
        }

        return $searchKey ? array_get($object, $searchKey, null) : $object;
    }

    /**
     * Set cache
     *
     * @return array
     */
    protected function setCache()
    {
        // Check if cache has expired
        if ($this->cache->expired() === false) {
            return;
        }

        // Instantiate values
        $values = array();

        // Get values from database
        foreach($this->database->table($this->config['table'])->get() as $setting)
        {
            $values[$setting->key] = json_decode($setting->value, true);
        }

        // Cache values
        $this->cache->set($values);
    }
}
