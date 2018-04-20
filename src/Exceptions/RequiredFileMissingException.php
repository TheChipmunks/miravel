<?php

namespace Miravel\Exceptions;

class RequiredFileMissingException extends BaseException
{
    protected $message = 'File "{file}" is not found';
}
