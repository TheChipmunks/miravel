<?php

namespace Miravel\Builders;

use Miravel\Exceptions\RequiredFileMissingException;
use Symfony\Component\Finder\Finder;
use Miravel\Facade as MiravelFacade;
use Miravel\CliCommandResult;
use Exception;

/**
 * Class LaravelMixThemeBuilder
 *
 * Build a theme with the help of the laravel-mix node package
 *
 * This class requires that laravel-mix be installed in your project.
 * Laravel 5.4 and above, just run  "npm install";
 * Laravel 5.3 and below, run "npm install laravel-mix"
 *
 * @package Miravel\Builders
 */
class LaravelMixThemeBuilder extends CommandLineThemeBuilder implements ThemeBuilderInterface
{
    protected $npmCommands = [
        'build'                => 'cd %s; npm run %s -- --env.mixfile=%s',
        'check-npm'            => 'npm -v',
        'check-package'        => 'npm list %s | grep %1$s',
        'check-package-global' => 'npm list -g %s | grep %1$s',
    ];

    protected $requiredNpmPackages = ['laravel-mix'];

    protected $defaultMixFileName = 'webpack.mix.js';

    /**
     * @var string
     */
    protected $mixFileName;

    /**
     * @var array
     */
    public $extensionList = ['scss', 'sass', 'less', 'styl', 'css', 'es5', 'es6', 'js'];

    /**
     * This builder only needs specific file types
     *
     * Dump only css and js files and their sources
     *
     * @return \Closure
     */
    public function getDumpFileFilter()
    {
        $extensions = $this->getExtensionListToDump();
        $preg_quote = function ($v) { return preg_quote($v, '/'); };

        $regex = collect($extensions)->map($preg_quote) ->implode('|');
        $regex = sprintf('/\.(%s)$/i', $regex);

        return function (Finder $finder) use ($regex) {
            $finder->name($regex);
        };
    }

    /**
     * @return array
     */
    public function getExtensionListToDump(): array
    {
        return (array)$this->extensionList;
    }

    public function getBuildCommand(): string
    {
        $command = $this->npmCommands['build'];

        $dir     = $this->getBuildDirectory();
        $env     = $this->getEnv();
        $mixfile = $this->getMixFileName();

        return sprintf($command, $dir, $env, $mixfile);
    }

    public function checkRequirements()
    {
        $this->report('Checking dependencies and requirements...');

        $this->checkNpm();

        $this->checkNpmPackages();

        $this->checkMixFile();

        $this->report('Requirement check complete.');
    }

    public function run()
    {
        $command = $this->getBuildCommand();

        $result = $this->runCliCommand($command);

        if (!$result->isSuccessful()) {

        }
    }

    public function getMixFileName(): string
    {
        if (!$this->mixFileName) {
            $themeConfig = $this->getTheme()->getConfig();

            $this->mixFileName = isset($themeConfig['mixfile']) ?
                (string)$themeConfig['mixfile'] :
                (string)$this->defaultMixFileName;
        }

        return $this->mixFileName;
    }

    public function getMixFilePath()
    {
        $buildDir = $this->getBuildDirectory();
        $mixfile  = $this->getDefaultMixFileName();

        return implode(DIRECTORY_SEPARATOR, [$buildDir, $mixfile]);
    }

    public function checkMixFile()
    {
        $mixFilePath = $this->getMixFilePath();

        if (!file_exists($mixFilePath) || !is_file($mixFilePath)) {
            MiravelFacade::exception(RequiredFileMissingException::class, ['file' => $mixFilePath], __FILE__, __LINE__);
        }
    }

    public function checkNpm()
    {
        $command = $this->npmCommands['check-npm'];

        $result = $this->runCliCommand($command);

        if (!$result->isSuccessful()) {
            $message = 'npm is required to run LaravelMix Theme Builder. ' .
                       'For information on how to install node.js and npm, ' .
                       'please visit https://docs.npmjs.com/getting-started/' .
                       'installing-node#install-npm--manage-npm-versions';

            throw new Exception($message);
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
                $message = 'npm package "%s" is required to run LaravelMix ' .
                           'Theme Builder. Try running "npm install -g %1$s"';

                $message = sprintf($message, $package);

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

    protected function reportBuildErrors(CliCommandResult $result)
    {
        $message = 'Errors were encountered while trying to build theme %s. ' .
                   'The last output line was: "%s"';

        $message = sprintf(
            $message,
            $this->getTheme()->getName(),
            $result->getLastOutputLine()
        );

        if ($cli = $this->getCli()) {
            $cli->error($message);
            if ($this->isDebugVerbosity()) {
                $this->showCommandOutput($result);
            } else {
                $this->showVerbosityInfo();
            }
        }

        MiravelFacade::error($message);
    }

    protected function reportBuildSuccess(CliCommandResult $result)
    {
        $message = 'Theme %s has been compiled.';
        $message = sprintf($message, $this->getTheme()->getName());

        if ($cli = $this->getCli()) {
            $cli->line($message);
        }

        MiravelFacade::info($message);
    }

    public function showCommandOutput(CliCommandResult $result)
    {
        if (!$cli = $this->getCli()) {
            return;
        }

        foreach ($result->getOutput() as $line) {
            $this->cli->line($line);
        }
    }

    public function showVerbosityInfo()
    {
        $message = 'To see the npm command output, run artisan miravel:build ' .
                   'with the -vvv flag.';

        $this->line($message);
    }

    public function report($message, $method = 'line')
    {
        if (!$cli = $this->getCli() || $this->isQuiet()) {
            return;
        }

        $cli->$method($message);
    }
}
