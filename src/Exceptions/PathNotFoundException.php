<?php

namespace Miravel\Exceptions;

class PathNotFoundException extends BaseException
{
    protected $message = 'Path "{path}" is not found.';
}
