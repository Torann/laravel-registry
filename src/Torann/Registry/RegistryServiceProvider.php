<?php namespace Torann\Registry;

use Illuminate\Support\ServiceProvider;

class RegistryServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('torann/registry');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerCache();
		$this->registerRegistry();
	}

    /**
     * Register the collection repository.
     *
     * @return void
     */
    protected function registerRegistry()
    {
        $this->app['registry'] = $this->app->share(function($app)
        {
            $config = $app->config->get('registry::config', array());

            return new Registry($app['db'], $app['registry.cache'], $config);
        });
    }

    /**
     * Register the collection repository.
     *
     * @return void
     */
    protected function registerCache()
    {
        $this->app['registry.cache'] = $this->app->share(function($app)
        {
            $meta = $app['config']->get('app.manifest');
            $timestampManager = $app->config->get('registry::timestamp_manager');

            return new Cache($meta, $timestampManager);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('registry');
	}

}