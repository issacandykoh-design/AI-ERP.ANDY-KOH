<?php

namespace Modules\MenuCustomization\Providers;

use Illuminate\Support\ServiceProvider;

class MenuCustomizationServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('menucustomization.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'menucustomization'
        );

        $this->mergeConfigFrom(
            module_path('MenuCustomization', 'Config/xss_ignore.php'),
            'menucustomization::xss_ignore'
        );
    }

    protected function registerViews()
    {
        $resourcePath = base_path('resources/views/menu-customization');
        $moduleSourcePath = __DIR__.'/../Resources/views';

        $paths = array_map(function ($path) {
            return $path.'/menu-customization';
        }, \Config::get('view.paths'));

        $this->loadViewsFrom(array_merge($paths, [$resourcePath, $moduleSourcePath]), 'menucustomization');
    }

    public function provides()
    {
        return [];
    }
}
