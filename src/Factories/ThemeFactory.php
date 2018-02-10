<?php

namespace Miravel\Factories;

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
            $theme = new Theme($themeName);
            static::$themeRegistry[$themeName] = $theme;

            // this must be called AFTER the theme has been registered
            $theme->initParentTheme();
        }

        return static::$themeRegistry[$themeName];
    }

    /**
     * Attempt to construct a theme and only return it if it's valid
     * (has a folder).
     *
     * @param string $themeName  the name of theme to construct.
     *
     * @return Theme
     */
    public static function makeAndValidate(string $themeName)
    {
        $theme = static::make($themeName);

        if ($theme && $theme->exists()) {
            return $theme;
        }
    }
}
