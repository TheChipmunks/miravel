<?php

namespace Miravel\Traits;

use Miravel\Exceptions\InvalidItemProperty;
use Miravel\Facade as MiravelFacade;
use Miravel\Utilities;

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
     * @param string $key      the name of the property to get, in dot notation
     *                         example: postcard.author.name or product.price
     * @param array $vartable  the variables defined in the calling scope
     *
     * @return mixed           the property value, if any
     */
    public function get(string $key, array $vartable)
    {
        $original = $key;
        $mapped   = $this->getMappedPropertyName($key);

        if (empty($mapped) || !is_string($mapped)) {
            MiravelFacade::exception(InvalidItemProperty::class, compact('original'), __FILE__, __LINE__);
        }

        return $this->getMultilevelProperty($mapped, $vartable);
    }


    protected function getMultilevelProperty(string $key, array $vartable)
    {
        $elements = Utilities::parseDataAccessExpression($key);

        if (empty($vartable)) {
            $vartable = $this->getData();
        }

        if (!$value = $vartable[$elements['varname']]) {
            $this->failToGetProperty($key);

            return;
        }

        if (empty($elements['steps'])) {
            return $value;
        }

        $steps = $elements['steps'];

        foreach ($steps as $step) {
            switch (gettype($value)) {
                case 'object':
                    $value = $value->$step ?? null;
                    break;
                case 'array':
                    $value = $value[$step] ?? null;
                    break;
                default:
                    $this->failToGetProperty($key);

                    return;
            }
        }

        return $value;
    }

    /**
     * Handle the event when the requested property is missing.
     *
     * @param string $property  the name of the missing property.
     */
    protected function failToGetProperty(string $property)
    {
        MiravelFacade::warning(sprintf(
            'Failed to get a property %s from element data.',
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
