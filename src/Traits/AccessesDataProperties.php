<?php

namespace Miravel\Traits;

use InvalidArgumentException;
use Miravel\Facade as MiravelFacade;
use Miravel\Exceptions\EmptyItemProperty;

/**
 * Trait AccessesDataProperties
 *
 * The slice of functionality for Miravel\Element that allows to do
 * $element->get($item, 'someproperty')
 * in the views, without knowing whether $item is an object or an array.
 *
 * @package Miravel
 */
trait AccessesDataProperties
{
    protected $propertyMap = [];

    /**
     * Get a property from an object or an associative array.
     *
     * @param string|array|object $item  the item to get the property from; OR,
     *                                   the property name to get, if data is
     *                                   represented by a 1-dim array.
     * @param string $property           the property name to get.
     *
     * @return mixed
     */
    public function get($item, string $property = null)
    {
        if (is_string($item)) {
            return $this->getPropertyFromData($item);
        }

        return $this->getPropertyFromItem($item, $property);
    }

    /**
     * Get a property from an object or an associative array.
     *
     * @param array|object $item  the item to get the property from.
     *
     * @param string $property    the property name to get.
     *
     * @return mixed
     */
    protected function getPropertyFromItem($item, string $property)
    {
        if (!is_object($item) && !is_array($item)) {
            $this->failToGetProperty($property);

            return;
        }

        $original = $property;
        $property = $this->getMappedPropertyName($property);

        if (empty($property)) {
            MiravelFacade::exception(EmptyItemProperty::class, compact('original'), __FILE__, __LINE__);
        }

        if (is_object($item)) {
            return $this->getPropertyFromObject($item, $property);
        }

        return $this->getPropertyFromArray($item, $property);
    }

    /**
     * Get the property from element data directly, assuming the data is a 1-dim.
     *
     * @param $propertyName  the property name to get.
     *
     * @return mixed|null    the property value, if any.
     */
    protected function getPropertyFromData($propertyName)
    {
        if (!method_exists($this, 'getData')) {
            return;
        }

        $data = $this->getData();

        $original     = $propertyName;
        $propertyName = $this->getMappedPropertyName($propertyName);

        if (empty($propertyName)) {
            MiravelFacade::exception(EmptyItemProperty::class, compact('original'), __FILE__, __LINE__);
        }

        if (is_object($data)) {
            return $this->getPropertyFromObject($data, $propertyName);
        }

        if (is_array($data)) {
            return $this->getPropertyFromArray($data, $propertyName);
        }
    }

    /**
     * Get a property from an object.
     *
     * @param object $item      the object to get the property from.
     * @param string $property  the name of the property to get.
     *
     * @return mixed|null
     */
    protected function getPropertyFromObject($item, string $property)
    {
        // 'object' argument type declaration not yet implemented into PHP,
        // so we have to emulate it.
        if (!is_object($item)) {
            $type = gettype($item);
            throw new InvalidArgumentException(
                '\\Miravel\\Element::getPropertyFromObject() ' .
                "expects an object, $type given");
        }

        return $item->$property ?? null;
    }

    /**
     * Get a property from an array.
     *
     * @param array $item       the associative array to get the property from.
     * @param string $property  the name of the property to get.
     *
     * @return mixed|null
     */
    protected function getPropertyFromArray(array $item, string $property)
    {
        return $item[$property] ?? null;
    }

    /**
     * Handle the event when the requested property is missing.
     *
     * @param string $property  the name of the missing property.
     */
    protected function failToGetProperty(string $property)
    {
        MiravelFacade::warning(sprintf(
            'Could not get a property %s from the item, ' .
            'because item is neither an array nor an object',

            $property
        ));
    }

    /**
     * If properties on the object passed by the developer to the element differ
     * from those designed by theme author, the developer may specify a property
     * map. So before looking up a property on the object, we will map it back.
     *
     * @param string $property  the property name to look up in the map.
     *
     * @return string
     */
    protected function getMappedPropertyName(string $property)
    {
        return $this->propertyMap[$property] ?? $property;
    }

    /**
     * Set the property alias map if one was provided by the developer.
     *
     * @param array $options  the options passed to the element.
     */
    protected function setupPropertyMap(array $options)
    {
        if (
            !isset($options['property_map']) ||
            !is_array($options['property_map'])
        ) {
            return;
        }

        $this->propertyMap = $options['property_map'];
    }
}
