<?php

namespace Miravel\Events;

use Miravel\Builders\ThemeBuilderInterface;
use Miravel\Theme;

class ThemeBuildStartedEvent
{
    /**
     * @var Theme
     */
    public $theme;

    /**
     * @var ThemeBuilderInterface
     */
    public $builder;

    public function __construct(Theme $theme, ThemeBuilderInterface $builder)
    {
        $this->theme   = $theme;
        $this->builder = $builder;
    }
}
