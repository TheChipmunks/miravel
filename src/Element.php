<?php

namespace Miravel;

use Miravel\Events\FinishElementRenderEvent;
use Miravel\Events\StartElementRenderEvent;
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
     * @var ThemeResource
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
     * @param string $name                  the name of the element, either
     *                                      absolute like
     *                                      'miravel::theme.elements.elementname',
     *                                      'theme.elementname'; or relative e.g.
     *                                      'elementname', 'elements.elementname'
     *                                      in which case it will be resolved
     *                                      from the theme hosting the current
     *                                      view.
     * @param array $data                   the data to populate the element
     *                                      with.
     * @param array $options                the options governing element
     *                                      appearance and behavior. Among them
     *                                      might be the 'property_map'
     * @param ThemeResource|null $resource  the resource containing the path to
     *                                      element file or directory, if
     *                                      precalculated elsewhere.
     */
    public function __construct(
        string $name,
        $data = [],
        array $options = [],
        BaseThemeResource $resource = null
    ) {
        $this->setOptions($options);
        $this->initResource($resource);
        $this->initName($name);
        $this->initPaths();
        $this->setData($data);
        $this->setupPropertyMap($options);
        $this->initSignature();
        $this->setExpectations();
    }

    /**
     * Set up the paths to directories and files used by this element.
     */
    protected function initPaths()
    {
        if (!$this->resource || !$this->resource instanceof ThemeResource) {
            return;
        }

        $this->paths['view'] = $this->resource->getViewFile();

        if ($this->resource->isDir()) {
            $this->paths['directory'] = $this->resource->getPathname();
        }
    }

    /**
     * If we were given an incomplete element name, prepend the theme name to it.
     *
     * @param $name
     */
    protected function initName($name)
    {
        if (static::isFullyQualifiedName($name)) {
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
    protected function initResource(BaseThemeResource $resource = null)
    {
        if (!$resource) {
            $resource = ResourceResolver::resolveElement($this->name);
        }

        $this->resource = $resource;

        if ($theme = $resource->getTheme()) {
            $this->setTheme($theme);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected static function isFullyQualifiedName(string $name)
    {
        return (false !== strpos($name, '.'));
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
     * @return null|ThemeResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ThemeResource $resource
     */
    public function setResource(ThemeResource $resource)
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
        event(new StartElementRenderEvent($this));

        $viewPath = $this->getViewPath();
        $viewVars = $this->prepareViewVars();

        if ($viewPath) {
            $view = View::file($viewPath, $viewVars);
            $output = $view ? $view->render() : '';
        } else {
            $output = '';
        }

        event(new FinishElementRenderEvent($this));

        return $output;
    }

    /**
     * Get the view name that can be resolved by FileViewFinder.
     *
     * @return string  the view name in Laravel format (dot separated).
     */
    protected function getViewPath()
    {
        return $this->paths['view'] ?? '';
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
