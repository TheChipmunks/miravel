<?php

namespace Miravel\Builders;

use Symfony\Component\Console\Output\OutputInterface;
use Miravel\Events\ThemeBuildDumpCompletedEvent;
use Miravel\Events\ThemeBuildFinishedEvent;
use Miravel\Events\ThemeBuildStartedEvent;
use Illuminate\Console\Command;
use Miravel\Theme;

abstract class BaseThemeBuilder implements ThemeBuilderInterface
{
    /**
     * @var bool
     */
    protected $ancestryFlag = true;

    /**
     * @var string
     */
    protected $buildDirectory;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var Command
     */
    protected $cli;

    public function __construct(Theme $theme)
    {
        $this->setTheme($theme);

        $this->initBuildDirectory()
             ->initEnv();
    }

    public function initBuildDirectory(): ThemeBuilderInterface
    {
        $dir = $this->theme->getDefaultThemeDumpPath();

        $this->setBuildDirectory($dir);

        return $this;
    }

    public function initEnv(): ThemeBuilderInterface
    {
        $this->setEnv(app()->environment());

        return $this;
    }

    /**
     * @return Theme
     */
    public function getTheme(): Theme
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     *
     * @return ThemeBuilderInterface
     */
    public function setTheme(Theme $theme): ThemeBuilderInterface
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAncestryFlag(): bool
    {
        return $this->ancestryFlag;
    }

    /**
     * @param bool $ancestryFlag
     *
     * @return ThemeBuilderInterface
     */
    public function setAncestryFlag(bool $ancestryFlag): ThemeBuilderInterface
    {
        $this->ancestryFlag = $ancestryFlag;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @param string $env
     *
     * @return ThemeBuilderInterface
     */
    public function setEnv(string $env): ThemeBuilderInterface
    {
        $this->env = $env;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuildDirectory(): string
    {
        return $this->buildDirectory;
    }

    /**
     * @param string $buildDirectory
     *
     * @return ThemeBuilderInterface
     */
    public function setBuildDirectory(string $buildDirectory): ThemeBuilderInterface
    {
        $this->buildDirectory = $buildDirectory;

        return $this;
    }

    public function report($message, $method = 'line')
    {
        if (!($cli = $this->getCli()) || $this->isQuiet()) {
            return;
        }

        $cli->$method($message);
    }

    /**
     * @return null|Command
     */
    public function getCli()
    {
        return $this->cli;
    }

    /**
     * @param Command $cli
     *
     * @return ThemeBuilderInterface
     */
    public function setCli(Command $cli): ThemeBuilderInterface
    {
        $this->cli = $cli;

        return $this;
    }

    /**
     * Copy theme files (that are necessary for build) to a temporary location.
     * Normally this must respect theme hierarchy and overrides. Hierarchical
     * lookups may be turned off via the ancestry flag (setAncestryFlag(false))
     *
     * @return $this
     */
    public function dumpTheme()
    {
        $destination    = $this->getBuildDirectory();
        $ancestry       = $this->getAncestryFlag();
        $finderModifier = $this->getDumpFileFilter();

        $this->theme->dumpFileTree($destination, $ancestry, $finderModifier);

        event(new ThemeBuildDumpCompletedEvent($this->theme, $this));

        return $this;
    }

    /**
     * @return callable|void
     */
    public function getDumpFileFilter()
    {
        //
    }

    public function isDebugVerbosity()
    {
        if (!$cli = $this->getCli()) {
            return false;
        }

        $verbosity = $cli->getOutput()->getVerbosity();

        return OutputInterface::VERBOSITY_DEBUG === $verbosity;
    }

    public function isQuiet()
    {
        if (!$cli = $this->getCli()) {
            return;
        }

        return $cli->option('quiet');
    }

    /**
     * Execute the build, dispatching the relevant events along the way
     */
    public function build()
    {
        event(new ThemeBuildStartedEvent($this->theme, $this));

        $this->execute();

        event(new ThemeBuildFinishedEvent($this->theme, $this));
    }

    // to be implemented in child classes
    abstract protected function execute();
}
