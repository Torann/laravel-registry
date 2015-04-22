<?php namespace CompareAsiaGroup\Registry;

use Illuminate\Filesystem\Filesystem

class Registry {

     /**
     * Filesystem manager instance
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Parent Array Object for config
     *
     * @var Array
     */
    protected $registryparent;

    /**
     * Registry config
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Array $config
     */
    public function __construct($config = array())
    {
        $this->filesystem = Storage::disk('registry');
        $this->registryparent = array();
        $this->config = $config;
        $this->init();
    }

    /**
     * Initialise
     *
     */
    public function init()
    {
        $files = $this->filesystem->files($this->config['locale']);
        foreach ($files as $file) {
            $filename = pathinfo("./".$file, PATHINFO_FILENAME);
            $this->registryparent[$filename] = json_decode($file->get(), true);
        }
        return true;
    }

    /**
     * Get value from registry
     *
     * @param  string  $component
     * @param  string  $key
     * @return mixed
     */
    public function get($component, $key)
    {
        $value = null;

        if (array_key_exists($component, $this->registryparent))
        {
            if (array_key_exists($key, $this->registryparent[$component])) {
                $value = $this->registryparent[$component][$key];
            }
        }

        return (! is_null($value)) ? $value : false;
    }

    /**
     * Store value into registry
     *
     * @param  string $component
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function set($component, $key, $value)
    {
        if (!array_key_exists($component, $this->registryparent)) {
            $this->registryparent[$component] = array();
        }

        if(!array_key_exists($key, $this->registryparent[$component])) {
            $this->registryparent[$component][$key] = $value;
        } else {
            if ($this->registryparent[$component][$key] !== $value) {
                $this->registryparent[$component][$key] = $value;
            }
        }

        return true;

    }

    /**
     * Overwrite existing value from registry
     *
     * @param  string $component
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function overwrite($component, $key, $value)
    {

        $this->registryparent[$component][$key] = $value;

        return true;
    }

    /**
     * Store an array
     *
     * @param  string $component
     * @return bool
     */
    public function store($component)
    {
        // @TODO write config component to file.
        $this->filesystem->put($component.".json", json_encode($this->registryparent[$component]));
    }

    /**
     * Remove existing value from registry
     *
     * @param  string $component
     * @param  string $key
     * @return bool
     */
    public function forget($component, $key = null)
    {
        if ($key != null) {
            unset($this->registryparent[$component]);

        } else {
            unset($this->registryparent[$component][$key]);
        }
    }

    /**
     * Clear registry
     *
     */
    public function flush()
    {
        foreach ($this->registryparent as $component)
        {
            unset($this->registryparent[$component]);
        }

        $this->init();
    }

    /**
     * Fetch all values
     *
     * @param  mixed $component
     * @return mixed
     */
    public function all($component = null)
    {
        if ($component === null)
        {
            return $this->registryparent;
        }
        else
        {
            return $this->registryparent[$component];
        }
    }
}
