<?php

namespace Miravel\Exceptions;

class ThemeDumpException extends BaseException
{
    protected $message = 'Error copying theme file "{file}" to destination "{dest}"';
}
