<?php

namespace Miravel\Exceptions;

class InvalidDataTypeException extends BaseException
{
    protected $message = 'Invalid data type detected in element "{element}", ' .
                         'required "{required}", got "{got}"';
}
