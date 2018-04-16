<?php

namespace Miravel\Console\Commands;

use Illuminate\Console\Command;
use Miravel\Build;

class MiravelBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:build {name}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Miravel Build';
    
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $Name = $this->argument('name');
        $Build = new Build($Name);
        $Build->generate();
        
        $IsSuccess = false;
        foreach ($Build->execute() as $Info){
            if(strstr($Info, "Compiled successfully") || $IsSuccess){
                $this->info($Info);
                $IsSuccess = true;
            } else {
                $this->warn($Info);
            }
        }
    }
}
