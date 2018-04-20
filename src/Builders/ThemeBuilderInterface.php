<?php

namespace Miravel\Builders;

use Miravel\Theme;

interface ThemeBuilderInterface
{
    public function setTheme(Theme $theme): ThemeBuilderInterface;

    public function build();
}
