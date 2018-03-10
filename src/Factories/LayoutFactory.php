<?php

namespace Miravel\Factories;

use Miravel\Exceptions\LayoutNotFoundException;
use Miravel\Layout;

/**
 * Class LayoutFactory
 *
 * The Layout Factory class.
 *
 * @package Miravel
 */
class LayoutFactory extends BaseViewFactory
{
    /**
     * @var string
     */
    protected static $viewType = 'layouts';

    /**
     * Make an instance of the requested layout. View names will be resolved by
     * Miravel rules (see the ViewNameResolver class).
     *
     * @param string $name  layout name, may be absolute such as
     *                      "miravel::theme.layouts.name" or relative such as
     *                      "theme.layoutname" or just "layoutname" (will be
     *                      looked up in current theme).
     *
     * @return Layout|null  return the layout if it exists, null otherwise.
     */
    public static function make(string $name)
    {
        $resource = static::resolveResource($name);

        if (!$resource) {
            Miravel::exception(LayoutNotFoundException::class, compact('name'), __FILE__, __LINE__);
        }

        $layout = new Layout($resource);

        if ($layout->exists()) {
            return $layout;
        }
    }
}
