<?php

namespace Miravel\Exceptions;

class EmptyItemProperty extends BaseException
{
    protected $message = 'Item property name is empty after mapping ' .
                         '(originally provided: "{original}")';
}
