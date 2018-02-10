<?php

namespace Miravel;

use Miravel\Exceptions\MissingConversionMethodException;
use Miravel\Traits\ConvertsData;
use Miravel;

/**
 * Class ElementData
 *
 * A class that represents data provided as element input.
 *
 * @package Miravel
 */
class ElementData
{
    use ConvertsData;

    const DATATYPE_2DIM_COLLECTION  = '2DCollection';
    const DATATYPE_2DIM_ARRAY       = '2DArray';
    const DATATYPE_2DIM_TRAVERSABLE = '2DTraversable';
    const DATATYPE_1DIM_ARRAY       = '1DArray';
    const DATATYPE_1DIM_OBJECT      = '1DObject';
    const DATATYPE_SCALAR           = 'scalar';
    const DATATYPE_UNKNOWN          = 'unknown';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * ElementData constructor.
     *
     * @param mixed $data  the data provided to the element by the calling view.
     *                     Despite an array is used as default, it may be just
     *                     about anything.
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Convert the original data to the format expected by the element.
     *
     * @param string $collectionFormat  the expected format of the data.
     * @param string|null $itemFormat   if data is expected as a 2D, then we can
     *                                  also expect each of its items to be an
     *                                  'object' or an 'array'.
     *
     * @return mixed
     */
    public function getAs(string $collectionFormat, string $itemFormat = null)
    {
        $method = 'getAs' . ucfirst($collectionFormat);

        if (is_callable([$this, $method])) {
            return $this->$method($itemFormat);
        }

        // the expected format is missing. The conversion will not be performed.
        Miravel::warning(sprintf(
            'ElementData is missing the method "%s" ' .
            'to convert the data into specified format.',
            $method
        ));

        return $this->getRaw();
    }

    /**
     * Get the data as was originally provided to the element.
     *
     * @return mixed
     */
    public function getRaw()
    {
        return $this->data;
    }

    /**
     * Get the form that the data was provided in. See this class' constants for
     * possible variants.
     *
     * @return string
     */
    public function getDataFormat()
    {
        return $this->analyzeDataType();
    }
}
