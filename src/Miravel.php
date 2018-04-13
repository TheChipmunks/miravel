<?php

namespace Miravel;

use Miravel\Traits\ProvidesBladeDirectives;
use Miravel\Factories\ElementFactory;
use Miravel\Factories\LoggerFactory;
use Miravel\Factories\LayoutFactory;
use Miravel\Factories\ThemeFactory;
use Miravel\Traits\RendersHtmlCode;
use Miravel\Traits\Loggable;
use Illuminate\View\View;

/**
 * Class Miravel
 *
 * The main service class.
 *
 * @package Miravel
 */
class Miravel
{
    use Loggable, ProvidesBladeDirectives, RendersHtmlCode;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var View
     */
    protected $currentView;

    /**
     * @var string
     */
    protected $topLevelElementName;

    /**
     * Miravel constructor.
     */
    public function __construct()
    {
        $this->initLogging();
    }

    /**
     * Initiate the logging, based on user's settings.
     */
    public function initLogging()
    {
        $logger = LoggerFactory::make();

        $this->setLogger($logger);
    }

    /**
     * Create the requested element and render it.
     *
     * @param string $name    the element name.
     * @param array $data     the data to populate the element with.
     * @param array $options  the options defining element's appearance and
     *                        behavior.
     *
     * @return string
     */
    public function element(
        string $name,
        $data = [],
        array $options = []
    ): string {
        $element = $this->makeElement($name, $data, $options);

        return $element->render();
    }

    /**
     * Return the layout currently being used.
     *
     * @return null|Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the layout currently being used.
     *
     * @param Layout $layout  the layout object.
     * @param bool $setTheme  if true, will set the current theme to the one the
     *                        layout belongs to.
     *
     * @return Miravel
     */
    public function setLayout(Layout $layout, $setTheme = true): Miravel
    {
        $this->layout = $layout;

        if ($setTheme && $theme = $layout->getTheme()) {
            $this->setTheme($theme);
        }

        return $this;
    }

    /**
     * Create a layout object from layout name.
     *
     * @param string $layoutName  the layout name.
     *
     * @return null|Layout        the layout object, or null if no valid layout
     *                            can be created by this name.
     */
    public function makeLayout(string $layoutName)
    {
        return LayoutFactory::make($layoutName);
    }

    /**
     * Tell whether a view is a miravel layout.
     *
     * @param string $viewName  a namespaced viewname, e.g.
     *                          "miravel::theme.layouts.layoutname"
     *
     * @return bool
     */
    public function isMiravelLayout(string $viewName): bool
    {
        $parser = new ViewNameParser($viewName);

        return $parser->isMiravelLayout();
    }

    /**
     * Tell whether a view is a miravel element.
     *
     * @param string $viewName  a namespaced viewname, e.g.
     *                          "miravel::theme.elements.elementname"
     *
     * @return bool
     */
    public function isMiravelElement(string $viewName): bool
    {
        $parser = new ViewNameParser($viewName);

        return $parser->isMiravelElement();
    }

    /**
     * Get the currently used theme.
     *
     * @return null|Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set the currently used theme.
     *
     * @param Theme $theme  the theme to set.
     *
     * @return Miravel
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Shortcut to getting miravel config values without "miravel." prefix
     *
     * @param string $name         the configuration name, dot notation.
     * @param null|mixed $default  the default value, if missing from config
     *
     * @return mixed
     */
    public function getConfig($name, $default = null)
    {
        return config("miravel.$name", $default);
    }

    /**
     * Shortcut to setting miravel config values without "miravel." prefix
     *
     * @param string $name  the configuration name, dot notation.
     * @param mixed $value  the value to set
     *
     * @return void
     */
    public function setConfig($name, $value)
    {
        config(["miravel.$name" => $value]);
    }

    /**
     * Create an instance of the theme.
     *
     * @param string $themeName  the theme name.
     *
     * @return Theme
     */
    public function makeTheme(string $themeName): Theme
    {
        return ThemeFactory::make($themeName);
    }

    /**
     * Create and return an instance of the theme, but only if it points to a
     * valid directory; otherwise return null.
     *
     * @param string $themeName  the theme name.
     *
     * @return Theme
     */
    public function makeAndValidateTheme(string $themeName)
    {
        return ThemeFactory::makeAndValidate($themeName);
    }

    /**
     * Create an Element.
     *
     * @param string $name    the element name.
     * @param array $data     the data to populate the element with.
     * @param array $options  the options defining element's appearance and
     *                        behavior.
     *
     * @return Element
     */
    public function makeElement(
        string $name,
        $data = [],
        array $options = []
    ): Element {
        return ElementFactory::make($name, $data, $options);
    }

    /**
     * The function that will run on all rendered views. This lets Miravel know
     * about the current view being rendered and current layout being used.
     *
     * This function is registered as a composer at the beginning of application
     * pipeline (by ThemeServiceProvider).
     *
     * @param View $view  the view being rendered.
     *
     * @return void
     */
    public function composer(View $view)
    {
        $this->setCurrentView($view);

        $viewName = $view->getName();

        if (
            $this->isMiravelLayout($viewName) &&
            $layout = $this->makeLayout($viewName)
        ) {
            $this->setLayout($layout);
        }
    }


    public function registerTopLevelRenderingElement(string $elementName)
    {
        if (!$this->topLevelElementName) {
            $this->topLevelElementName = $elementName;
        }
    }

    public function unregisterTopLevelRenderingElement(string $elementName)
    {
        if ($this->topLevelElementName == $elementName) {
            $this->topLevelElementName = null;
        }
    }

    public function getTopLevelRenderingElement()
    {
        return $this->topLevelElementName;
    }

    /**
     * Get the current view being rendered.
     *
     * @return null|View
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }

    /**
     * Set the current view being rendered.
     *
     * @param View $view  the view object.
     *
     * @return Miravel
     */
    public function setCurrentView(View $view)
    {
        $this->currentView = $view;

        return $this;
    }

    /**
     * If the current view belongs to a theme, instantiate and return the theme.
     *
     * @return Theme|void
     */
    public function getCurrentViewParentTheme()
    {
        $view = $this->getCurrentView();

        if ($view && ($theme = Utilities::viewBelongsToTheme($view))) {
            return $theme;
        }
    }

    /**
     * Get the theme that initiated current rendering procedure. It might be the
     * parent theme of current top level element, or the theme hosting the view
     * currently being rendered.
     */
    public function getCurrentTheme()
    {
        if ($elementName = $this->getTopLevelRenderingElement()) {
            $themeName = substr($elementName, 0, strpos($elementName, '.'));

            return $this->makeAndValidateTheme($themeName);
        }

        return $this->getCurrentViewParentTheme();
    }

}
