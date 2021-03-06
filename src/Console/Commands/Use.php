<?php

namespace Miravel\Console\Commands;

use Illuminate\Console\Command;

class UseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:use';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use a template in the application';

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
