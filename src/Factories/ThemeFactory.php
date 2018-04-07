<?php

namespace Miravel\Factories;

use Miravel\Facade as MiravelFacade;
use Miravel\Utilities;
use Miravel\Theme;

/**
 * Class ThemeFactory
 *
 * The class that instantiates themes.
 *
 * @package Miravel
 */
class ThemeFactory
{
    /**
     * All already instantiated themes are cached, to avoid having to construct
     * them twice.
     *
     * @var array
     */
    protected static $themeRegistry = [];

    /**
     * Make a theme.
     *
     * @param string $themeName  the name of the theme to construct.
     *
     * @return Theme
     */
    public static function make(string $themeName): Theme
    {
        if (!isset(static::$themeRegistry[$themeName])) {
            // when a theme is constructed it must be registered on the global registry
            static::instantiate($themeName);
        }

        return static::$themeRegistry[$themeName];
    }

    /**
     * Register the theme on the global registry.
     *
     * @param string $name  the name of the theme
     * @param Theme $theme  the theme object
     */
    public static function register(string $name, Theme $theme)
    {
        static::$themeRegistry[$name] = $theme;
    }

    /**
     * Attempt to construct a theme and only return it if it's valid
     * (has a folder).
     *
     * @param string $themeName  the name of theme to construct.
     *
     * @return Theme|void
     */
    public static function makeAndValidate(string $themeName)
    {
        $theme = static::make($themeName);

        if ($theme && $theme->exists()) {
            return $theme;
        }
    }

    /**
     * Instantiate the theme class, using the custom class if the class file
     * (theme.php) is provided, standard Theme class otherwise.
     *
     * @param string $themeName  the name of the theme.
     *
     * @return Theme
     */
    protected static function instantiate($themeName): Theme
    {
        // create a standard theme first and ask it to look for the class file
        $theme = new Theme($themeName);

        $className = static::getCustomClassName($theme);

        if ($className && is_subclass_of($className, Theme::class)) {
            return new $className($themeName);
        }

        return $theme;
    }

    /**
     * Ask theme to get its custom class name, which may be provided by theme
     * developer in theme.php. Try to autoload the class.
     * Return null if no custom class is available or it can't be autoloaded.
     *
     * @param Theme $theme
     *
     * @return Theme|void
     */
    protected static function getCustomClassName(Theme $theme)
    {
        $classFilePath = $theme->getResource(static::CLASS_FILE_NAME);

        // see if class file exists
        if (!$classFilePath || !is_file($classFilePath)) {
            return;
        }

        // see if class file contains a valid class definition
        if (!$className = Utilities::extractClassNameFromFile($classFilePath)) {
            return;
        }

        // if this class doesn't yet exist, try loading its definition
        if (!class_exists($className)) {
            include_once($classFilePath);
        }

        // autoloading failed
        if (!class_exists($className)) {
            return;
        }

        return $className;
    }

    protected static function getClassFileName()
    {
        return MiravelFacade::getConfig('class_file_name');
    }
}
