<?php

namespace Miravel\Exceptions;

class UnknownDataTypeException extends BaseException
{
    protected $message = 'Unknown input data type for element "{element}"';
}
