<?php

namespace Miravel\Exceptions;

class InvalidItemProperty extends BaseException
{
    protected $message = 'Item property "{original}" mapped to an invalid value (string expected).';
}
