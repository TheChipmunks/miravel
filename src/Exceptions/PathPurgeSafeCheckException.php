<?php

namespace Miravel\Exceptions;

class PathPurgeSafeCheckException extends BaseException
{
    protected $message = 'Will not delete directory "{path}": safety check failed';
}
