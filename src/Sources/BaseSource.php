<?php

namespace Miravel\Sources;

abstract class BaseSource implements ThemeSourceInterface
{
    abstract public function get($theme);
}
