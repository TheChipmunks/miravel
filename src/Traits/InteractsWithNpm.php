<?php

namespace Miravel\Traits;

use Miravel\CliCommandResult;
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
        'check-npm'                 => 'npm -v',
        'check-package'             => 'npm list %s',
        'check-package-global'      => 'npm list -g %s',
    ];

    public function checkNpm()
    {
        $command = $this->npmCommands['check-npm'];

        $result = Utilities::runCliCommand($command);

        if (!$result->isSuccessful()) {
            throw new Exception($this->getNpmRequiredMessage());
        }

        $npmversion = $result->getLastOutputLine();

        return $npmversion;
    }

    public function getRequiredNpmPackages(): array
    {
        return [];
    }

    public function checkNpmPackages()
    {
        $packages = $this->getRequiredNpmPackages();
        $found    = [];

        foreach ($packages as $package) {
            if (!$version = $this->checkNpmPackage($package)) {
                $message = $this->getPackageRequiredMessage();
                $message = sprintf($message, $package);

                throw new Exception($message);
            }
            $found[$package] = $version;
        }

        return $found;
    }

    public function checkNpmPackage($package)
    {
        foreach (['check-package', 'check-package-global'] as $env) {
            $version = $this->checkNpmPackageInEnv($package, $env);
            if (!$version instanceof CliCommandResult) {
                return $version;
            }
        }

        return false;
    }

    public function checkNpmPackageInEnv(string $package, string $env)
    {
        $check = $this->npmCommands[$env];
        $check = sprintf($check, $package);

        $result = Utilities::runCliCommand($check);

        if (!$result->isSuccessful()) {
            return $result;
        }

        $output = $result->getOutput();
        if (!count($output)) {
            return $result;
        }

        if (!$version = $this->extractPackageVersion($package, $output)) {
            return $result;
        }

        return $version;
    }

    public function getNpmRequiredMessage()
    {
        return 'npm is required. ' .
               'To learn how to install node.js and npm, ' .
               'please visit https://docs.npmjs.com/getting-started/' .
               'installing-node#install-npm--manage-npm-versions';
    }

    public function getPackageRequiredMessage()
    {
        return 'npm package "%s" is required.' .
               'Try running "npm install %1$s"';
    }

    protected function extractPackageVersion(string $package, array $outputLines)
    {
        foreach ($outputLines as $line) {
            $pos = strpos($line, '@');
            if (false === strpos($line, $package) || false === $pos) {
                continue;
            }

            return substr($line, $pos + 1);
        }
    }
}
