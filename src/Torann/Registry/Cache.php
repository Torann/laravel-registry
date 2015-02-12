<?php namespace Torann\Registry;

class Cache {

    /**
     * Timestamp key.
     *
     * @var string
     */
    protected $timestampKey = 'TorannRegistryTimeStamp';

    /**
     * Timestamp Manager
     *
     * @var null|\Torann\Registry\Timestamps\TimestampInterface
     */
    protected $timestampManager;

    /**
     * Path to the cached file.
     *
     * @var string
     */
    protected $path;

    /**
     * Collection of cached entries.
     *
     * @var array
     */
    protected $entries = array();

    /**
     * Create a new instance.
     *
     * @param  string $cachePath
     * @param  string $timestampManager
     */
    public function __construct($cachePath, $timestampManager)
    {
        $this->path = $cachePath.DIRECTORY_SEPARATOR.'torann_registry.json';

        // Instantiate timestamp manager
        if (class_exists($timestampManager)) {
            $this->timestampManager = new $timestampManager();
        }

        // Load values
        if (file_exists($this->path)) {
            $this->entries = json_decode(file_get_contents($this->path), true);
        }
    }

    /**
     * Get a all cached values.
     *
     * @param  mixed $default
     * @return mixed
     */
    public function all($default = array())
    {
        return (! empty($this->entries) ) ? $this->entries : $default;
    }

    /**
     * Get a key's value from the cache.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return isset($this->entries[$key]) ? $this->entries[$key] : $default;
    }

    /**
     * Add a key's value to the cache.
     *
     * @param  string  $key
     * @param  string  $value
     * @return bool
     */
    public function add($key, $value)
    {
        $this->entries[$key] = $value;

        return $this->save();
    }

    /**
     * Set value to the cache.
     *
     * @param  array  $value
     * @return bool
     */
    public function set(array $value)
    {
        $this->entries = $value;

        return $this->save();
    }

    /**
     * Remove a key from cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function remove($key)
    {
        unset($this->entries[$key]);

        return $this->save();
    }

    /**
     * Remove all cached entries.
     *
     * @return bool
     */
    public function flush()
    {
        $this->entries = null;

        return $this->save();
    }

    /**
     * Get last updated timestamp.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->get($this->timestampKey);
    }

    /**
     * Check if cached as expired.
     *
     * @return bool
     */
    public function expired()
    {
        // Update if empty
        if (empty($this->entries)) {
            return true;
        }

        // Check timestamps
        if ($this->timestampManager) {
            return $this->timestampManager->check($this->getTimestamp());
        }

        return false;
    }

    /**
     * Save to cache.
     *
     * @return bool
     */
    public function save()
    {
        // Update time - now
        $updated = time();

        // Update timestamp
        $this->entries[$this->timestampKey] = $updated;

        // Update timestamp manager
        if ($this->timestampManager) {
            $this->timestampManager->update($updated);
        }

        return (bool) file_put_contents($this->path, json_encode($this->entries));
    }
}
