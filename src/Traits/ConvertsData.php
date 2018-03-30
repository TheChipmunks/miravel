<?php

namespace Miravel\Traits;

use Miravel\Exceptions\InvalidDataTypeException;
use Miravel\Exceptions\UnknownDataTypeException;
use Miravel\Facade as MiravelFacade;
use Illuminate\Support\Collection;
use Miravel\ElementData;
use IteratorAggregate;
use Traversable;
use Throwable;
use stdClass;

/**
 * Trait ConvertsData
 *
 * The slice of functionality for Miravel\Element that allows to enforce the
 * desired format on the arbitrary data passed to the element from outside.
 *
 * @package Miravel
 */
trait ConvertsData
{
    /**
     * @var mixed
     */
    protected $data = [];

    /**
     * Confirm that data is a collection of arrays or an array of arrays.
     *
     * Returns true if data can be iterated and each of its elements
     * is either an object or an array. An empty array will also fit here.
     *
     * This function isn't but a fair approximation. Override it in your element
     * if you need more precise logic.
     *
     * @return bool
     */
    protected function dataIsTwoDimension()
    {
        // Laravel Collections are also Traversables
        if (!$this->dataIsTraversable() && !is_array($this->data)) {
            return false;
        }

        foreach ($this->data as $item) {
            if ($this->isScalar($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Confirm that data can be accessed as a hash (an object or an array) and
     * at least one of its properties is scalar (not an array or object itself).
     *
     * This function isn't but a fair approximation. Override it in your element
     * if you need more precise logic.
     *
     * @return bool
     */
    protected function dataIsOneDimension()
    {
        return !$this->dataIsScalar() && !$this->dataIsTwoDimension();
    }

    /**
     * Tell if our data is a Laravel Collection.
     *
     * @return bool
     */
    protected function dataIsCollection()
    {
        return $this->data instanceof Collection;
    }

    /**
     * Tell if our data is a Traversable (which is an array or an Iterator).
     *
     * @return bool
     */
    protected function dataIsTraversable()
    {
        return $this->data instanceof Traversable;
    }

    /**
     * Tell if our data is scalar, that is neither an array nor an object.
     *
     * @return bool
     */
    protected function dataIsScalar()
    {
        return $this->isScalar($this->data);
    }

    /**
     * For the needs of this application, we define "scalar" as anything neither
     * an array nor an object (incl. null, resource etc)
     *
     * @param mixed $var  the variable to examine.
     *
     * @return bool
     */
    protected function isScalar($var)
    {
        return !is_array($var) && !is_object($var);
    }

    /**
     * Get our data as a two-dimensional array.
     *
     * @param null|string $itemType  if provided, also convert each item of the
     *                               resulting collection to the specified type,
     *                               which can be 'array' or 'object'.
     *                               If omitted, collection items are left as is
     *
     * @return array
     */
    public function getAs2DArray(string $itemType = null)
    {
        $dataType = $this->analyzeDataType();

        switch ($dataType) {
            case ElementData::DATATYPE_2DIM_COLLECTION:
                return $this->convert2DCollectionTo2DArray($itemType);

            case ElementData::DATATYPE_2DIM_ARRAY:
            case ElementData::DATATYPE_2DIM_TRAVERSABLE:
                return $this->convert2DTraversableTo2DArray($itemType);

            case ElementData::DATATYPE_SCALAR:
                $item = ['value' => $this->data];
                $item = $this->requireItemType($item, $itemType);
                return [$item];

            case ElementData::DATATYPE_1DIM_OBJECT:
            case ElementData::DATATYPE_1DIM_ARRAY:
                $item = $this->requireItemType($this->data, $itemType);
                return [$item];

            default:
                MiravelFacade::exception(UnknownDataTypeException::class, ['name' => $this->name ?? ''], __FILE__, __LINE__);
        }
    }

    /**
     * Get our data as a two-dimensional Collection ().
     *
     * Of course we could shorten the code by just returning
     * collect($this->getAs2DArray()) but in some cases this would result in
     * double conversions back and forth between an array and a collection.
     *
     * @param null|string $itemType optionally also convert each item of the
     * resulting collection to an 'array' or 'object'. If omitted, no
     * transformation is applied to Collection items.
     *
     * @return Collection
     */
    public function getAs2DCollection(string $itemType = null): Collection
    {
        $dataType = $this->analyzeDataType();

        switch ($dataType) {
            case ElementData::DATATYPE_2DIM_COLLECTION:
                return $this->convertItemsIn2DCollection($itemType);

            case ElementData::DATATYPE_2DIM_ARRAY:
            case ElementData::DATATYPE_2DIM_TRAVERSABLE:
                return $this->convert2DTraversableTo2DCollection($itemType);

            case ElementData::DATATYPE_SCALAR:
                $item = ['value' => $this->data];
                $item = $this->requireItemType($item, $itemType);
                return collect([$item]);

            case ElementData::DATATYPE_1DIM_OBJECT:
            case ElementData::DATATYPE_1DIM_ARRAY:
                $item = $this->requireItemType($this->data, $itemType);
                return collect([$item]);

            default:
                MiravelFacade::exception(UnknownDataTypeException::class, ['name' => $this->name ?? ''], __FILE__, __LINE__);
        }
    }

    /**
     * Get our data as a 1-dimensional array. If a 2D array is provided as input,
     * this method will pick its first element. If you are authoring a theme
     * and need another behavior for your element, feel free to override this
     * function in your element class.
     *
     * @return array
     */
    public function getAs1DArray(): array
    {
        if ($this->dataIsOneDimension() || $this->dataIsScalar()) {
            return $this->smartCastToArray($this->data);
        }

        // Data is in fact 2-dimensional (an array or a Traversable).
        // We'll take the first element.
        $element = $this->getFirstElement($this->data);

        // If there were zero elements, return something we still can ask for
        // properties.
        if (is_null($element)) {
            $element = [];
        }

        return $this->smartCastToArray($element);
    }

    /**
     * Get our data as an object. If a 2D array is provided as input, this method
     * will pick its first element. If you are authoring a theme and need another
     * behavior for your element, feel free to override this function in your
     * element class.
     *
     * @return object
     */
    public function getAs1DObject()
    {
        if ($this->dataIsOneDimension()) {
            return $this->data = (object)$this->data;
        }

        if ($this->dataIsScalar()) {
            return $this->data = (object)['value' => $this->data];
        }

        // Data is in fact 2-dimensional (an array or a Traversable).
        // We'll take the first element.

        $element = $this->getFirstElement($this->data);

        // If there were zero elements, return something we still can ask for
        // properties.
        if (is_null($element)) {
            $element = new stdClass;
        }

        return (object)$element;
    }

    /**
     * Get our data as a string that can be used in a template.
     *
     * This method will try its best to convert the input data to a string,
     * including calling the "toString" method on an object if available; but if
     * everything fails, we will log a warning and return an empty string.
     *
     * @return string
     */
    public function getAsScalar()
    {
        if (is_object($this->data) && is_callable([$this->data, 'toString'])) {
            return $this->data->toString();
        }

        try {
            $result = (string)$this->data;
        } catch (Throwable $e) {
            MiravelFacade::warning(sprintf(
                'Element "%s" failed to convert data to a string',
                ($this->name ?? '')
            ));

            $result = '';
        }

        return $result;
    }

    /**
     * Get the first item from a collection of items.
     *
     * @param array|Collection|Traversable $series  the collection of items.
     *
     * @return null|mixed|void
     */
    protected function getFirstElement($series)
    {
        if ($series instanceof Collection) {
            return $series->first();
        }

        if ($series instanceof Traversable) {
            return $this->getFirstElementFromTraversable($series);
        }

        if (is_array($series)) {
            return count($series) ? reset($series) : null;
        }
    }

    /**
     * Get the first element from a collection represented by something other
     * than Laravel Collection.
     *
     * @param Traversable $object  the iterable collection.
     */
    protected function getFirstElementFromTraversable(Traversable $object)
    {
        $iterator = $this->data;
        while ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }

        $iterator->rewind();
        if (!$iterator->valid()) {
            return;
        }

        return $iterator->current();
    }

    /**
     * Convert anything to an array. This function will also try calling the
     * "toArray" method on an input object, if applicable.
     *
     * @param mixed $item  the value to convert to array.
     *
     * @return array
     */
    protected function smartCastToArray($item): array
    {
        if (is_object($item) && is_callable([$item, 'toArray'])) {
            return (array)$item->toArray();
        }

        if ($this->isScalar($item)) {
            return ['value' => $item];
        }

        return (array)$item;
    }

    /**
     * Convert an item to an array or an object, if explicitly requested.
     *
     * @param mixed $item            the variable to convert.
     * @param string|null $itemType  the requested type. If anything other than
     *                               'array' or 'object', the item will not be
     *                               transformed.
     *
     * @return mixed
     */
    protected function requireItemType($item, string $itemType = null)
    {
        switch ($itemType) {
            case 'array':
                return $this->smartCastToArray($item);
            case 'object':
                return (object)$item;
            default:
                return $item;
        }
    }

    /**
     * Examine our input data and tell which type it belongs to. See ElementData
     * for possible types.
     *
     * @return string
     */
    protected function analyzeDataType(): string
    {
        if ($this->dataIsScalar()) {
            return ElementData::DATATYPE_SCALAR;
        }

        if ($this->dataIsTwoDimension()) {
            if ($this->dataIsCollection()) {
                return ElementData::DATATYPE_2DIM_COLLECTION;
            }
            if (is_array($this->data)) {
                return ElementData::DATATYPE_2DIM_ARRAY;
            }
            return ElementData::DATATYPE_2DIM_TRAVERSABLE;
        }

        if (is_array($this->data)) {
            return ElementData::DATATYPE_1DIM_ARRAY;
        }

        if (is_object($this->data)) {
            return ElementData::DATATYPE_1DIM_OBJECT;
        }

        // Is there really any way we can get here?
        return ElementData::DATATYPE_UNKNOWN;
    }

    /**
     * Convert a 2D Laravel Collection to an array, and if itemType is requested,
     * along the way also convert all collection items to the requested type.
     *
     * @param string|null $itemType  the item type to convert the items to
     *                               ('array' or 'object'). Pass null if no
     *                               conversion is needed.
     *
     * @return array
     */
    protected function convert2DCollectionTo2DArray(string $itemType = null): array
    {
        $this->requireCollection(__FILE__, __LINE__);

        $converted = $this->data->each(
            function ($item) use ($itemType) {
                return $this->requireItemType($item, $itemType);
            }
        );

        return $converted->all();
    }

    /**
     * Convert a Traversable to an array, and if itemType is requested,
     * along the way also convert all collection items to the requested type.
     *
     * @param string|null $itemType  the item type to convert the items to
     *                               ('array' or 'object'). Pass null if no
     *                               conversion is needed.
     *
     * @return array
     */
    protected function convert2DTraversableTo2DArray(string $itemType = null): array
    {
        $this->requireTraversable(__FILE__, __LINE__);

        $converted = [];
        foreach ($this->data as $key => $item) {
            $converted[$key] = $this->requireItemType($item, $itemType);
        }

        return $converted;
    }

    /**
     * Convert a Traversable to a Collection, and if itemType is requested,
     * along the way also convert all collection items to the requested type.
     *
     * @param string|null $itemType  the item type to convert the items to
     *                               ('array' or 'object'). Pass null if no
     *                               conversion is needed.
     *
     * @return Collection
     */
    protected function convert2DTraversableTo2DCollection(string $itemType = null): Collection
    {
        $this->requireTraversable(__FILE__, __LINE__);

        $converted = $this->convert2DTraversableTo2DArray($itemType);

        return collect($converted);
    }

    /**
     * Convert all items in a Collection to the desired item type (array or
     * object).
     *
     * @param string|null $itemType  the item type to convert the items to
     *                               ('array' or 'object'). Pass null if no
     *                               conversion is needed.
     *
     * @return Collection
     */
    protected function convertItemsIn2DCollection(string $itemType = null): Collection
    {
        $this->requireCollection(__FILE__, __LINE__);

        $converted = $this->data->each(
            function ($item) use ($itemType) {
                return $this->requireItemType($item, $itemType);
            }
        );

        return $converted;
    }

    /**
     * Double-check that $this->data is an instance of Laravel Collection.
     *
     * @param $file  the file where the Exception was triggered
     * @param $line  the line where the Exception was triggered
     */
    protected function requireCollection($file, $line)
    {
        if (!$this->data instanceof Collection) {
            MiravelFacade::exception(
                InvalidDataTypeException::class,
                [
                    'element'  => $this->name ?? '',
                    'required' => 'Collection',
                    'got'      => gettype($this->data),
                ],
                $file,
                $line
            );
        }
    }

    /**
     * Double-check that $this->data is an instance of Traversable.
     *
     * @param $file  the file where the Exception was triggered
     * @param $line  the line where the Exception was triggered
     */
    protected function requireTraversable($file, $line)
    {
        if (!$this->data instanceof Traversable) {
            MiravelFacade::exception(
                InvalidDataTypeException::class,
                [
                    'element'  => $this->name ?? '',
                    'required' => 'Traversable',
                    'got'      => gettype($this->data),
                ],
                $file,
                $line
            );
        }
    }
}
