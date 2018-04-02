<?php

namespace Miravel\Console\Commands;

use Illuminate\Console\Command;

class MiravelMake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:make';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a blank theme';
    
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
        //
    }
}
