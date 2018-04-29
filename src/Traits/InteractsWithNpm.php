<?php

namespace Miravel\Traits;

trait InteractsWithNpm
{
    use RunsCliCommands;

    protected $npmCommands = [
        'check-npm'                 => 'npm -v',
        'check-package'             => 'npm list %s | grep %1$s',
        'check-package-global'      => 'npm list -g %s | grep %1$s',
    ];

    protected $requiredNpmPackages  = [];

    protected $packageRequiredMessage = 'npm package "%s" is required.' .
                                        'Try running "npm install -g %1$s"';

    protected $npmRequiredMessage = 'npm is required. ' .
                                    'To learn how to install node.js and npm, ' .
                                    'please visit https://docs.npmjs.com/getting-started/' .
                                    'installing-node#install-npm--manage-npm-versions';

    public function checkNpm()
    {
        $command = $this->npmCommands['check-npm'];

        $result = $this->runCliCommand($command);

        if (!$result->isSuccessful()) {
            throw new Exception($this->npmRequiredMessage);
        }

        $npmversion = $result->getLastOutputLine();
        $this->report(sprintf('npm found, version %s', $npmversion));
    }

    public function getRequiredNpmPackages(): array
    {
        return (array)$this->requiredNpmPackages;
    }

    public function checkNpmPackages()
    {
        $packages = $this->getRequiredNpmPackages();

        foreach ($packages as $package) {
            if (!$this->checkNpmPackage($package)) {
                $message = sprintf($this->packageRequiredMessage, $package);

                throw new Exception($message);
            }
        }
    }

    public function checkNpmPackage($package): bool
    {
        foreach (['check-package', 'check-package-global'] as $env) {
            if (true === $this->checkNpmPackageInEnv($package, $env)) {
                return true;
            }
        }

        return false;
    }

    public function checkNpmPackageInEnv(string $package, string $env)
    {
        $check = $this->npmCommands[$env];
        $check = sprintf($check, $package);

        $result = $this->runCliCommand($check);

        if ($result->isSuccessful() && count($result->getOutput()) >= 1) {
            $lastLine = $result->getLastOutputLine();
            $version = substr($lastLine, strpos($lastLine, '@') + 1);
            $this->report(sprintf('%s found, version %s', $package, $version));

            return true;
        }

        return $result;
    }

}
