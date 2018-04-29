<?php

namespace Miravel\Builders;

use Miravel\Exceptions\RequiredFileMissingException;
use Miravel\Traits\InteractsWithNpm;
use Symfony\Component\Finder\Finder;
use Miravel\Facade as MiravelFacade;
use Miravel\Traits\RunsCliCommands;
use Miravel\CliCommandResult;

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
class LaravelMixThemeBuilder extends CommandLineThemeBuilder implements
    ThemeBuilderInterface
{
    use RunsCliCommands, InteractsWithNpm;

    protected $buildCommand             = 'node %s NODE_ENV=%s %s' .
                                          ' --progress' .
                                          ' --hide-modules' .
                                          ' --config=%s' .
                                          ' --env.themepath=%s' .
                                          ' --env.mixfile=%s';

    protected $requiredNpmPackages      = ['laravel-mix', 'webpack', 'cross-env'];

    protected $crossEnvJs               = 'node_modules/cross-env/dist/bin/cross-env.js';

    protected $webpackJs                = 'node_modules/webpack/bin/webpack.js';

    protected $defaultWebpackConfig     = 'vendor/miravel/miravel/mix/webpack.config.js';

    protected $defaultMixFileName       = 'webpack.mix.js';

    protected $packageRequiredMessage   = 'npm package "%s" is required to run Laravel Mix ' .
                                          'Theme Builder. Try running "npm install -g %1$s"';

    protected $npmRequiredMessage       = 'npm is required to run Laravel Mix Theme Builder. ' .
                                          'To learn how to install node.js and npm, ' .
                                          'please visit https://docs.npmjs.com/getting-started/' .
                                          'installing-node#install-npm--manage-npm-versions';

    /**
     * @var string
     */
    protected $mixFileName;

    /**
     * @var array
     */
    public $extensionList = ['scss', 'sass', 'less', 'styl', 'css', 'es5', 'es6', 'js', 'json'];

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

        $regex = collect($extensions)->map($preg_quote)->implode('|');
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
        $command   = $this->buildCommand;
        $baseDir   = base_path() . DIRECTORY_SEPARATOR;
        $dir       = $this->getBuildDirectory() . DIRECTORY_SEPARATOR;
        $themePath = str_replace($baseDir, '', $dir);

        return sprintf($command, $this->crossEnvJs, $this->getEnv(), $this->webpackJs, $this->defaultWebpackConfig, $themePath, $themePath . $this->defaultMixFileName);
    }

    public function checkRequirements()
    {
        if ($cli = $this->getCli()) {
            if ($cli->option('skip-dep-checks')) {
                return;
            }
        }

        $this->report('Checking dependencies and requirements...');

        $this->checkNpm();

        $this->checkNpmPackages();

        $this->report('Requirement check complete.');
    }

    public function run()
    {
        $this->checkMixFile();

        $command = $this->getBuildCommand();

        $result = $this->runCliCommand($command);

//         $this->report(sprintf('Running %s', $command));

        if (!$result->isSuccessful()) {
            $this->reportBuildErrors($result);
        } else {
            $this->reportBuildSuccess($result);
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
        $mixfile  = $this->getMixFileName();

        return implode(DIRECTORY_SEPARATOR, [$buildDir, $mixfile]);
    }

    public function checkMixFile()
    {
        $mixFilePath = $this->getMixFilePath();

        if (!file_exists($mixFilePath) || !is_file($mixFilePath)) {
            MiravelFacade::exception(RequiredFileMissingException::class, ['file' => $mixFilePath], __FILE__, __LINE__);
        }
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
                $this->showVerbosityHint();
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

    public function showVerbosityHint()
    {
        $message = 'To see the npm command output, run artisan miravel:build ' .
                   'with the -vvv flag.';

        $this->line($message);
    }

}
