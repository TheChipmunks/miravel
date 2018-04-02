<?php

namespace Miravel\Console\Commands;

use Illuminate\Console\Command;

class MiravelPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:publish';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy theme assets to public directory';
    
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
