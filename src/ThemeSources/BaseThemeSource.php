<?php

namespace Miravel\ThemeSources;

abstract class BaseThemeSource implements ThemeSourceInterface
{
    abstract public function get($theme);
}
