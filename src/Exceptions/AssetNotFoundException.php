<?php

namespace Miravel\Exceptions;

class AssetNotFoundException extends BaseException
{
    protected $message = 'Asset "{asset}" cannot be found.';
}
