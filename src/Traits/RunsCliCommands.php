<?php

namespace Miravel\Traits;

use Miravel\CliCommandResult;

trait RunsCliCommands
{
    /**
     * @param string $command
     *
     * @return CliCommandResult
     */
    public function runCliCommand(string $command)
    {
        $return = 0;
        $output = [];

        exec($command, $output, $return);

        return new CliCommandResult($output, $return);
    }
}
