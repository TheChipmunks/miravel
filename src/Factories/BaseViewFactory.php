<?php

namespace Miravel\Factories;

use Miravel\Resources\BaseThemeResource;
use Miravel\ResourceResolver;

/**
 * Class BaseViewFactory
 *
 * The abstract class that holds common functions for ElementFactory and
 * LayoutFactory.
 *
 * @package Miravel
 */
abstract class BaseViewFactory
{
    /**
     * To be defined in extending classes
     *
     * @var string
     */
    protected static $viewType;

    /**
     * Resolve the view name to file or directory path using ViewNameResolver.
     *
     * @param string $name              the view name.
     *
     * @return null|BaseThemeResource|void  the resource containing path to file or
     *                                  directory.
     */
    protected static function resolveResource(string $name)
    {
        return static::getResolver()->resolve($name);
    }

    /**
     * Get an instance of View Name Resolver with the $viewType set to necessary
     * value.
     *
     * @return ResourceResolver
     */
    public static function getResolver(): ResourceResolver
    {
        return new ResourceResolver(static::$viewType);
    }
}
