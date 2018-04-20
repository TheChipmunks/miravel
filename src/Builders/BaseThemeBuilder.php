<?php

namespace Miravel\Builders;

use Miravel\Theme;

abstract class BaseThemeBuilder implements ThemeBuilderInterface
{
    /**
     * @var Theme
     */
    protected $theme;

    public function __construct(Theme $theme)
    {
        $this->setTheme($theme);
    }

    public function setTheme(Theme $theme): ThemeBuilderInterface
    {
        $this->theme = $theme;

        return $this;
    }

    // implementation must be provided by child classes
    abstract public function build();
}
