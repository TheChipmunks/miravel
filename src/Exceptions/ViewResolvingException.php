<?php

namespace Miravel\Exceptions;

class ViewResolvingException extends BaseException
{
    protected $message = 'Error processing {directive} in "{callingview}": {error}';
}
