<?php

namespace Miravel\Exceptions;

class ElementNotFoundException extends BaseException
{
    protected $message = 'Element {name} not found.';
}
