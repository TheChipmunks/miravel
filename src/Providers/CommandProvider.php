<?php
namespace Miravel\Providers;

use Illuminate\Support\ServiceProvider;
use Miravel\Console\Kernel as MiravelKernel;
use Illuminate\Contracts\Console\Kernel as LaravelKernel;
use Miravel\Console\Commands\BuildCommand;

class CommandProvider extends ServiceProvider
{
    
    protected $defer = true;
    
    protected $commands = [
        'Build' => 'command.miravel.build'
    ];
    
    
    public function register()
    {
        $this->registerCommands($this->commands);
    }
    
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            call_user_func_array([$this, "register{$command}Command"], []);
        }
        
        $this->commands(array_values($commands));
    }
    
    protected function registerBuildCommand()
    {
        $this->app->singleton('command.miravel.build', function ($app) {
            return new BuildCommand();
        });
    }
    
    public function provides()
    {
        return array_values($this->commands);
    }
}