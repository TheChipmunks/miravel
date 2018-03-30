<?php

namespace Miravel;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * Class Facade
 *
 * The Miravel Facade.
 *
 * @package Miravel
 */
class Facade extends IlluminateFacade
{
    protected static function getFacadeAccessor() {
        return 'miravel';
    }
}
