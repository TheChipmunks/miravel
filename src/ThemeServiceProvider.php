<?php

namespace Miravel;

use Illuminate\Support\ServiceProvider;
use Blade;
use View;

class ThemeServiceProvider extends ServiceProvider
{
    protected $paths = [
        'config' => __DIR__ . '/../resources/config.php',
        'themes' => __DIR__ . '/../resources/themes',
    ];

    // protected $defer = true;

    public function boot()
    {
        $this->registerPublishedVendorFiles();

        $this->registerViewNameComposer();

        $this->registerBladeExtension();

        $this->registerBladeDirectives();

        $this->loadViewsFrom($this->paths['themes'], 'miravel');
	}

    public function register()
    {
        $this->registerMiravelService();

        $this->registerViewFileFinder();

        $this->mergeConfigFrom($this->paths['config'], 'miravel');
    }

    public function provides()
    {
        return ['miravel'];
    }

    protected function registerMiravelService()
    {
        $this->app->singleton('miravel', function () {
            return new Miravel;
        });
	}

    protected function registerViewNameComposer()
    {
        View::composer('*', function($view) {
            \Miravel::composer($view);
        });
    }

    protected function registerViewFileFinder()
    {
        $this->app->singleton('view.finder', function($app) {
            return new FileViewFinder(
                $app['files'],
                $app['config']['view.paths'],
                null
            );
        });
    }

    protected function registerBladeExtension()
    {
        Blade::extend(function ($viewContents) {
            $compiler = new BladeCompilerExtension($viewContents);

            return $compiler->process();
        });
    }

    protected function registerPublishedVendorFiles()
    {
        $this->publishes([
            $this->paths['config'] => config_path('miravel.php'),
        ], 'config');

        $this->publishes([
            $this->paths['themes'] => resource_path('views/vendor/miravel'),
        ], 'views');
    }

    protected function registerBladeDirectives()
    {
        $directiveMap = (array)config('miravel.blade_directive_map');
        $miravel      = app()->make('miravel');

        foreach ($directiveMap as $directive => $alias) {
            $method  = 'directive' . ucfirst($directive);
            if (method_exists($miravel, $method)) {
                Blade::directive(
                    $alias,
                    function ($expression) use ($miravel, $method) {
                        return call_user_func([$miravel, $method], $expression);
                    }
                );
            }
        }
    }
}
