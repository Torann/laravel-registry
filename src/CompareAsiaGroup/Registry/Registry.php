<?php namespace CompareAsiaGroup\Registry;

use Illuminate\Filesystem\Filesystem;

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
        foreach($this->config['locales'] as $locale) {

            $files = $this->filesystem->files($locale);

            foreach ($files as $file) {
                $filename = pathinfo("./".$file, PATHINFO_FILENAME);
                $this->registryparent[$locale][$filename] = json_decode($file->get(), true);
            }

        }
        return true;
    }

    /**
     * Get value from registry
     *
     * @param  string  $locale
     * @param  string  $component
     * @param  string  $key
     * @return mixed
     */
    public function get($locale, $component, $key)
    {
        $value = null;

        if (array_key_exists($component, $this->registryparent[$locale]))
        {
            if (array_key_exists($key, $this->registryparent[$locale][$component])) {
                $value = $this->registryparent[$locale][$component][$key];
            }
        }

        return (! is_null($value)) ? $value : false;
    }

    /**
     * Store value into registry
     *
     * @param  string locale
     * @param  string $component
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function set($locale, $component, $key, $value)
    {
        if (!array_key_exists($component, $this->registryparent[$locale])) {
            $this->registryparent[$locale][$component] = array();
        }

        if(!array_key_exists($key, $this->registryparent[$locale][$component])) {
            $this->registryparent[$locale][$component][$key] = $value;
        } else {
            if ($this->registryparent[$locale][$component][$key] !== $value) {
                $this->registryparent[$locale][$component][$key] = $value;
            }
        }

        return true;

    }

    /**
     * Overwrite existing value from registry
     *
     * @param  string $locale
     * @param  string $component
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    public function overwrite($locale, $component, $key, $value)
    {

        $this->registryparent[$locale][$component][$key] = $value;

        return true;
    }

    /**
     * Store an array
     *
     * @param  string $component
     * @return bool
     */
    public function store($locale, $component)
    {
        // @TODO write config component to file.
        $this->filesystem->put($locale."/".$component.".json", json_encode($this->registryparent[$locale][$component]));
    }

    /**
     * Remove existing value from registry
     *
     * @param  string $locale
     * @param  string $component
     * @param  string $key
     * @return bool
     */
    public function forget($locale, $component, $key = null)
    {
        if ($key != null) {
            unset($this->registryparent[$locale][$component]);

        } else {
            unset($this->registryparent[$locale][$component][$key]);
        }
    }

    /**
     * Clear registry
     *
     */
    public function flush()
    {
        foreach ($this->registryparent as $locale)
        {
            unset($this->registryparent[$locale]);
        }

        $this->init();
    }

    /**
     * Fetch all values
     *
     * @param  string $locale
     * @param  string $component
     * @return mixed
     */
    public function all($locale, $component = null)
    {
        if ($component === null)
        {
            return $this->registryparent[$locale];
        }
        else
        {
            return $this->registryparent[$locale][$component];
        }
    }
}
