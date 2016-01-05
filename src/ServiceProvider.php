<?php namespace Alawrence\Ipboard;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    private function getConfigPath(){
        return __DIR__ . '/../config/ipboard.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'ipboard');

        $this->app['ipboard'] = $this->app->share(function($app){
            return new Ipboard();
        });

        $this->app['command.ipboard.test'] = $this->app->share(
            function ($app) {
                return new Console\TestCommand($app['ipboard']);
            }
        );

        $this->commands(array('command.ipboard.test'));
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->getConfigPath() => config_path('ipboard.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
