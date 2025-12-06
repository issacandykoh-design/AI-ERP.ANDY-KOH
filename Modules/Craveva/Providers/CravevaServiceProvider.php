<?php

namespace Modules\Craveva\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CravevaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Blade::componentNamespace('Modules\\Craveva\\Views\\Components', 'craveva');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('craveva.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'craveva'
        );

        $this->mergeConfigFrom(
            module_path('craveva', 'Config/xss_ignore.php'),
            'craveva::xss_ignore'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = base_path('resources/views/modules/craveva');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ]);

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/craveva';
        }, \Config::get('view.paths')), [$sourcePath]), 'craveva');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = base_path('resources/lang/modules/craveva');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'craveva');
        }
        else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'craveva');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}
