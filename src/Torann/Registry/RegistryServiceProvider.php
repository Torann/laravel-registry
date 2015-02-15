<?php namespace Torann\Registry;

use Illuminate\Support\ServiceProvider;
use Torann\Registry\Commands\MigrationCommand;

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
        $this->publishes([
            __DIR__.'/../../config/registry.php' => config_path('registry.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../../migrations/' => base_path('/database/migrations')
        ], 'migrations');
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
            $config = $app->config->get('registry', array());

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
            $meta = $app->config->get('registry.cache_path');
            $timestampManager = $app->config->get('registry.timestamp_manager');
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