<?php

namespace Miravel\Exceptions;

class LayoutNotFoundException extends BaseException
{
    protected $message = 'Layout {name} not found.';
}
