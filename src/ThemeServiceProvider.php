<?php

namespace Miravel;

// general
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

// events
use Miravel\Listeners\ElementRenderEventListener;
use Miravel\Events\FinishElementRenderEvent;
use Miravel\Events\ElementRenderStartedEvent;

//commands
use Miravel\Console\Commands\PublishCommand;
use Miravel\Console\Commands\UpdateCommand;
use Miravel\Console\Commands\CloneCommand;
use Miravel\Console\Commands\BuildCommand;
use Miravel\Console\Commands\MakeCommand;
use Miravel\Console\Commands\GetCommand;
use Miravel\Console\Commands\UseCommand;


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

        $this->registerCommands();

        $this->registerEvents();

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
            $this->paths['themes'] => config('miravel.paths.app'),
        ], 'themes');
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

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GetCommand::class,
                UpdateCommand::class,
                CloneCommand::class,
                BuildCommand::class,
                PublishCommand::class,
                UseCommand::class,
                MakeCommand::class
            ]);
        }
    }

    protected function registerEvents()
    {
        // ElementRenderEventListener will listen to both startRender and
        // finishRender events

        Event::listen(
            ElementRenderStartedEvent::class,
            ElementRenderEventListener::class
        );

        Event::listen(
            ElementRenderFinishedEvent::class,
            ElementRenderEventListener::class
        );
    }
}
