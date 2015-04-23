<?php namespace CompareAsiaGroup\Registry;

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
        $this->publishes([
            __DIR__.'/../../config/registry.php' => config_path('registry.php'),
        ]);

    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
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

            return new Registry($config);
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