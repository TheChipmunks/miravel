<?php

namespace Miravel\Builders;

use Miravel\Events\ThemeBuildDumpCompletedEvent;
use Miravel\Events\ThemeBuildFinishedEvent;
use Miravel\Events\ThemeBuildStartedEvent;
use Symfony\Component\Finder\Finder;

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
class LaravelMixThemeBuilder extends BaseThemeBuilder implements ThemeBuilderInterface
{
    /**
     * @var bool
     */
    public $ancestryFlag = true;

    /**
     * @var array
     */
    public $extensionList = ['scss', 'sass', 'less', 'styl', 'css', 'es5', 'es6', 'js'];

    public function build()
    {
        event(new ThemeBuildStartedEvent($this->theme, $this));

        $this->dumpTheme();

        event(new ThemeBuildDumpCompletedEvent($this->theme, $this));

        $this->runBuild();

        event(new ThemeBuildFinishedEvent($this->theme, $this));
    }

    protected function getThemeDumpLocation()
    {
        return $this->theme->getDefaultThemeDumpPath();
    }

    protected function getAncestryFlag(): bool
    {
        return (bool)$this->ancestryFlag;
    }

    protected function dumpTheme()
    {
        $destination = $this->getThemeDumpLocation();
        $modifier    = $this->getFinderModifier();
        $ancestry    = $this->getAncestryFlag();

        $this->theme->dumpFileTree($destination, $ancestry, $modifier);
    }

    /**
     * Dump only css and js files and their sources
     *
     * @return \Closure
     */
    protected function getFinderModifier()
    {
        $extensions = $this->getExtensionListToDump();

        $regex = collect($extensions)->map(function ($v) { return preg_quote($v, '/'); })
                                     ->implode('|');

        $regex = sprintf('/\.(%s)$/i', $regex);

        return function (Finder $finder) use ($regex) {
            $finder->name($regex);
        };
    }

    /**
     * This builder only needs files with certain extensions
     *
     * @return array
     */
    protected function getExtensionListToDump(): array
    {
        return (array)$this->extensionList;
    }

    protected function runBuild()
    {
        $location = $this->getThemeDumpLocation();

        echo "Running build of $location\n";
    }
}
