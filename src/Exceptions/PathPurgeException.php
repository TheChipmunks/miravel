<?php

namespace Miravel\Exceptions;

class PathPurgeException extends BaseException
{
    protected $message = 'Error purging directory "{path}": filesystem error, check that the directory can be deleted and re-created by php.';
}
