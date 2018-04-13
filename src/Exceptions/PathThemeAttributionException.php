<?php

namespace Miravel\Exceptions;

class PathThemeAttributionException extends BaseException
{
    protected $message = 'Failed to find the theme that path "{path}" belongs to.';
}
