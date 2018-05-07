<?php

namespace Miravel\Traits;

use Miravel\Utilities;
use Exception;

/**
 * Trait InteractsWithNpm
 *
 * @package Miravel\Traits
 */
trait InteractsWithNpm
{
    protected $npmCommands = [
        'check-node'                 => 'node -v',
        'check-laravel-mix'          => 'node -p "var modulePath = require.resolve(\'laravel-mix\'); modulePath = modulePath.substring(0, modulePath.indexOf(\'laravel-mix\') + \'laravel-mix\'.length + 1); require(modulePath + \'package.json\').version;"',
    ];

    public function checkNodeVersion()
    {
        $command = $this->npmCommands['check-node'];

        $result = Utilities::runCliCommand($command);

        if (!$result->isSuccessful()) {
            throw new Exception($this->getNodeRequiredMessage());
        }

        $version = $result->getLastOutputLine();

        return $version;
    }

    public function checkMixVersion()
    {
        $command = $this->npmCommands['check-laravel-mix'];

        $result = Utilities::runCliCommand($command);

        if (!$result->isSuccessful()) {
            throw new Exception($this->getMixRequiredMessage());
        }

        $version = $result->getLastOutputLine();

        return $version;
    }

}
