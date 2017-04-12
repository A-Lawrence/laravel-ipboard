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

        $this->app['ipboard'] = $this->app->singleton("ipboard", function($app){
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
        \Validator::extend("is_csv_numeric", function($attribute, $value, $parameters, $validator){
            return preg_match("/^[0-9,]+$/i", $value);
        });

        \Validator::extend("is_csv_alpha", function($attribute, $value, $parameters, $validator){
            return preg_match("/^[A-Z,]+$/i", $value);
        });

        \Validator::extend("is_csv_alphanumeric", function($attribute, $value, $parameters, $validator){
            return preg_match("/^[0-9A-Z,]+$/i", $value);
        });
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
