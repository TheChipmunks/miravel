<?php

namespace Miravel;

use Miravel\Events\FinishElementRenderEvent;
use Miravel\Events\ElementRenderStartedEvent;
use Miravel\Traits\AccessesDataProperties;
use Miravel\Resources\BaseThemeResource;
use Miravel\Traits\ExpectsDataFormats;
use Illuminate\Support\Facades\View;
use Miravel\Traits\HasOptions;

/**
 * Class Element
 *
 * The default class representing an element.
 *
 * @package Miravel
 */
class Element
{
    use AccessesDataProperties, ExpectsDataFormats, HasOptions;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ElementData
     */
    protected $data;

    /**
     * @var string
     */
    protected $dataVarName = 'data';

    /**
     * @var BaseThemeResource
     */
    protected $resource;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var array
     */
    protected $paths = [];

    protected $signature;

    /**
     * Element constructor.
     *
     * @param string $name                      the name of the element, either absolute
     *                                          (e.g. 'miravel::theme.elements.elementname',
     *                                          'theme.elementname'); or relative e.g.
     *                                          'elementname', 'elements.elementname'
     *                                          in which case it will be resolved from the theme
     *                                          hosting the current view.
     * @param array $data                       the data to populate the element with.
     * @param array $options                    the options governing element
     *                                          appearance and behavior. Among them
     *                                          might be the 'property_map'
     * @param BaseThemeResource|null $resource  the resource containing the path to
     *                                          element file or directory, if
     *                                          precalculated elsewhere.
     */
    public function __construct(
        string $name,
        $data = [],
        array $options = [],
        BaseThemeResource $resource
    ) {
        $this->setOptions($options);
        $this->initResource($resource);
        $this->initName($name);
        $this->setData($data);
        $this->setupPropertyMap($options);
        $this->initSignature();
        $this->setExpectations();
    }

    /**
     * If we were given an incomplete element name, prepend the theme name to it.
     *
     * @param $name
     */
    protected function initName($name)
    {
        if ($this->isFullyQualifiedName($name)) {
            $this->name = $name;
        } else {
            $this->name = $this->prependThemePrefix($name);
        }
    }

    protected function initSignature()
    {
        $this->signature = static::makeSignature();
    }

    /**
     * @param BaseThemeResource $resource
     */
    protected function initResource(BaseThemeResource $resource)
    {
        $this->resource = $resource;

        $this->setTheme($resource->getCallingTheme());
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function isFullyQualifiedName(string $name)
    {
        $themeName = $this->getTheme()->getName();
        $themeName .= '.';

        return 0 === strpos($name, $themeName);
    }

    /**
     * Set the data to populate this element. Data will be stored in a special
     * ElementData object that can manipulate it before rendering.
     *
     * @param array $data  the element input data.
     */
    public function setData($data = [])
    {
        $this->data = new ElementData($data);
    }

    /**
     * Get the Element data in the format that was set by an expectation. If no
     * specific format is expected, return the data as is.
     *
     * @return mixed
     */
    public function getData()
    {
        $dataFormat = $this->getExpectedDataFormat();
        $itemFormat = $this->getExpectedItemFormat();

        if (!$dataFormat) {
            return $this->data->getRaw();
        }

        return $this->data->getAs($dataFormat, $itemFormat);
    }

    /**
     * @return null|BaseThemeResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param BaseThemeResource $resource
     */
    public function setResource(BaseThemeResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the format in which the data was originally provided. See ElementData
     * class constants for possible return values.
     *
     * @return string
     */
    public function getDataFormat()
    {
        return $this->data->getDataFormat();
    }

    /**
     * Render the element and return its output as a string. Enables you to
     * override this in your element class should you need custom rendering
     * method for your element.
     *
     * @return string  the rendered element.
     */
    public function render(): string
    {
        event(new ElementRenderStartedEvent($this));

        $viewPath = $this->getViewName();
        $viewVars = $this->prepareViewVars();

        if ($viewPath) {
            $view = View::file($viewPath, $viewVars);
            $output = $view ? $view->render() : '';
        } else {
            $output = '';
        }

        event(new ElementRenderFinishedEvent($this));

        return $output;
    }

    /**
     * Get the view name that can be resolved by FileViewFinder.
     *
     * @return string  the view name in Laravel format (dot separated).
     */
    protected function getViewName()
    {
        return $this->getResource()->getViewFile();
    }

    /**
     * Prepare the view variables to feed to the element view template.
     *
     * @return array  the view variables (element itself, data, and options)
     */
    protected function prepareViewVars()
    {
        $dataVarKey = (string)$this->dataVarName;

        return [
            'element'   => $this,
            $dataVarKey => $this->getData(),
            'options'   => $this->getOptions(),
        ];
    }

    /**
     * Get the name of this element, as it was called from the view.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    protected function prependThemePrefix(string $name)
    {
        $themeName = $this->getTheme()->getName();

        return implode('.', [$themeName, $name]);
    }

    public static function makeSignature()
    {
        return str_random(32);
    }

    public function getSignedName()
    {
        return implode('.', [$this->getName(), $this->getSignature()]);
    }
}
