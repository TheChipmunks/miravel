<?php

namespace Miravel\Traits;

use Miravel\ElementData;

/**
 * Trait ExpectsDataFormats
 *
 * A slice of functionality for Miravel\Element which allows to set the expected
 * input data format. If set, the element will automatically try to convert data
 * to the desired format before rendering.
 *
 * @package Miravel
 */
trait ExpectsDataFormats
{
    /**
     * See the ElementData class for available formats.
     *
     * @var string
     */
    protected $expectedDataFormat;

    /**
     * How you'd like to access the elements of a collection.
     * Supported are 'object' and 'array'.
     *
     * @var string
     */
    protected $expectedItemFormat;

    /**
     * Get the expected data format. See the ElementData class for available
     * formats.
     *
     * @return null|string
     */
    public function getExpectedDataFormat()
    {
        return $this->expectedDataFormat;
    }

    /**
     * Set the expected data format. See the ElementData class for available
     * formats. While the setter doesn't check that the format is listed, but
     * specifying anything beyound supported formats will not have any effect.
     *
     * @param string $format
     */
    public function setExpectedDataFormat(string $format)
    {
        $this->expectedDataFormat = $format;
    }

    /**
     * Get the expected format of items inside a collection. Only useful when the
     * expected data format is 2-Dim.
     *
     * @return null|string
     */
    public function getExpectedItemFormat()
    {
        return $this->expectedItemFormat;
    }

    /**
     * Set the expected format of items inside a collection. Only useful when the
     * expected data format is 2-Dim.
     *
     * @param string $format
     */
    public function setExpectedItemFormat(string $format)
    {
        $this->expectedItemFormat = $format;
    }

    /**
     * Tell the Element to convert its data to a 2D array. Its items will not be
     * touched.
     */
    public function expects2DArray()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_ARRAY);
    }

    /**
     * Tell the Element to convert its data to a 2D array before rendering. Its
     * items will also be converted to arrays.
     */
    public function expects2DArrayWithArrays()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_ARRAY);
        $this->setExpectedItemFormat('array');
    }

    /**
     * Tell the Element to convert its data to a 2D array before rendering. Its
     * items will also be converted to objects.
     */
    public function expects2DArrayWithObjects()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_ARRAY);
        $this->setExpectedItemFormat('object');
    }

    /**
     * Tell the Element to convert its data to a Laravel Collection. Its items
     * will not be touched.
     */
    public function expects2DCollection()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_COLLECTION);
    }

    /**
     * Tell the Element to convert its data to a Laravel Collection. Each of its
     * items will be converted to an array.
     */
    public function expects2DCollectionWithArrays()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_COLLECTION);
        $this->setExpectedItemFormat('array');
    }

    /**
     * Tell the Element to convert its data to a Laravel Collection. Each of its
     * items will be converted to an object.
     */
    public function expects2DCollectionWithObjects()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_2DIM_COLLECTION);
        $this->setExpectedItemFormat('object');
    }

    /**
     * Tell the Element to convert its data to a 1D array before rendering.
     */
    public function expects1DArray()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_1DIM_ARRAY);
    }

    /**
     * Tell the Element to convert its data to an object before rendering.
     */
    public function expects1DObject()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_1DIM_OBJECT);
    }

    /**
     * Tell the Element to convert its data to a string before rendering.
     */
    public function expectsScalar()
    {
        $this->setExpectedDataFormat(ElementData::DATATYPE_SCALAR);
    }

    /**
     * This function is called from Element constructor. By default it does not
     * set any expectation. You can override it in your element class and call
     * one of the above methods, to set the expected data format from the
     * beginning of element lifespan.
     */
    protected function setExpectations()
    {
        //
    }
}
