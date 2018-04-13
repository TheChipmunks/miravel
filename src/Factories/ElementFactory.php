<?php

namespace Miravel\Factories;

use Miravel\Exceptions\ElementNotFoundException;
use Miravel\Resources\BaseThemeResource;
use Miravel\Facade as MiravelFacade;
use Miravel\Utilities;
use Miravel\Element;

/**
 * Class ElementFactory
 *
 * The Element Factory class.
 *
 * @package Miravel
 */
class ElementFactory extends BaseViewFactory
{
    const DEFAULT_ELEMENT_CLASS = '\Miravel\Element';

    /**
     * @var string
     */
    protected static $viewType  = 'elements';

    /**
     * Make an instance of requested element with given data and options.
     *
     * @param string $name    element name, may be absolute such as
     *                        "miravel::theme.elements.name" or relative such as
     *                        "theme.elementname" or just "elementname" (will be
     *                        looked up in current theme).
     * @param mixed $data     the data to populate the element with, e.g. texts,
     *                        image urls etc.
     * @param array $options  options that could change element's appearance and
     *                        behavior.
     *
     * @return Element        the instantiated element.
     */
    public static function make(
        string $name,
        $data = [],
        array $options = []
    ): Element {
        if (!$resource = static::resolveResource($name)) {
            MiravelFacade::exception(ElementNotFoundException::class, compact('name'), __FILE__, __LINE__);
        }

        return static::makeFromResource($name, $resource, $data, $options);
    }

    public static function makeFromResource(
        string $name,
        BaseThemeResource $resource,
        $data = [],
        array $options = []
    ) {
        $className = static::getCustomClassName($resource);

        if (
            !$className ||
            !is_subclass_of($className, static::DEFAULT_ELEMENT_CLASS)
        ) {
            $className = static::DEFAULT_ELEMENT_CLASS;
        }

        return new $className($name, $data, $options, $resource);
    }

    /**
     * Given a directory that (presumably) contains element.php, try looking up
     * the name of the custom element class. If such a class is found, autoload
     * it if necessary.
     *
     * @param BaseThemeResource $resource  the resource containing the directory
     *                                     path.
     *
     * @return string|void                 the class name that can be instantiated,
     *                                     or null if the class does not exist.
     */
    protected static function getCustomClassName(BaseThemeResource $resource)
    {
        $classFilePath = $resource->getClassFile();

        // see if class file exists in directory
        if (!$classFilePath) {
            return;
        }

        // see if class file contains a valid class definition
        if (!$className = Utilities::extractClassNameFromFile($classFilePath)) {
            return;
        }

        // if this class doesn't yet exist, try loading its definition
        if (!class_exists($className)) {
            include_once($classFilePath);
        }

        // autoloading failed
        if (!class_exists($className)) {
            return;
        }

        return $className;
    }
}

