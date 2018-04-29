<?php

namespace Miravel\Builders;

use Miravel\Traits\RunsCliCommands;

abstract class CommandLineThemeBuilder extends BaseThemeBuilder implements
    ThemeBuilderInterface
{
    protected $command = '';

    public function execute()
    {
        $this->checkRequirements();

        $this->dumpTheme();

        $this->run();
    }

    public function checkRequirements()
    {
        if ($cli = $this->getCli()) {
            if ($cli->option('skip-dep-checks')) {
                return;
            }
        }

        // throw an exception if a dependency is missing, such as npm package
    }

    public function getCliCommand()
    {
        return $this->command;
    }

    // to be implemented in child classes
    abstract public function run();
}
