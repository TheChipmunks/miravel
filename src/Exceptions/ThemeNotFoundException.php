<?php

namespace Miravel\Exceptions;

class ThemeNotFoundException extends BaseException
{
    protected $message = 'Theme {name} not found.';
}
