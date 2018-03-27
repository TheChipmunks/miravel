<?php

namespace Miravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;

class BuildCommand extends Command
{
    use DetectsApplicationNamespace;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:build';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Miravel Build';
    
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Hi');
    }
    
    
}