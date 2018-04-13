<?php

namespace Miravel\Exceptions;

class UnknownResourceFstypeException extends BaseException
{
    protected $message = 'Unknown resource filesystem type "{path}", must be a file or a directory';
}
