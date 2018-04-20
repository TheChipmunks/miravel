<?php

namespace Miravel\Console\Commands;

use Miravel\Exceptions\ThemeNotFoundException;
use Miravel\Facade as MiravelFacade;
use Illuminate\Console\Command;

class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miravel:build {theme} {--publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build theme assets from sources';

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
        $name = $this->argument('theme');

        if (!$theme = MiravelFacade::makeAndValidateTheme($name)) {
            MiravelFacade::exception(ThemeNotFoundException::class, ['theme' => $name], __FILE__, __LINE__);
        }

        $theme->build();

        // if ($this->option('publish')) {
        //     $theme->publish();
        // }
    }
}
