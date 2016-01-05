<?php namespace Alawrence\IPBoardAPI;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/ipboardapi.php';
        $this->mergeConfigFrom($configPath, 'ipboardapi');

        $this->app->bind("ipboardapi", "Alawrence\IPBoardAPI\IPBoardAPI");

        $this->app['command.ipboardapi.test'] = $this->app->share(
            function ($app) {
                return new Console\TestCommand($app['ipboardapi']);
            }
        );

        $this->commands(array('command.ipboardapi.test'));
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('alawrence/laravel-ipboardapi');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('ipboardapi', 'command.ipboardapi.test');
    }
}
