<?php

namespace Miravel;

use Miravel\Facade as MiravelFacade;

/**
 * Class ResourceResolver
 *
 * A class responsible for resolving resource names ('theme.element') to
 * filesystem paths.
 *
 * @package Miravel
 */
class ResourceResolver
{
    protected $viewType;

    /**
     * ViewNameResolver constructor.
     *
     * @param string $viewType  may be 'elements', 'layouts', or 'templates'
     */
    public function __construct(string $viewType)
    {
        $this->viewType = $viewType;
    }

    /**
     * Resolve an element view.
     *
     * @param string $viewName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveElement(string $viewName)
    {
        $resolver = new static('elements');

        return $resolver->resolve($viewName);
    }

    /**
     * Resolve a layout view.
     *
     * @param string $viewName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveLayout(string $viewName)
    {
        $resolver = new static('layouts');

        return $resolver->resolve($viewName);
    }

    /**
     * Resolve a template view.
     *
     * @param string $viewName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveTemplate(string $viewName)
    {
        $resolver = new static('templates');

        return $resolver->resolve($viewName);
    }

    /**
     * Translates view names to file or directory paths. File path is the actual
     * view path, while directory is the one containing the template file.
     *
     * @param string $viewName          the absolute name (e.g.
     *                                  "miravel::theme.elements.name",
     *                                  "theme.elements.name",
     *                                  "theme.elementname")
     *                                  or relative name (like "elementname").
     *                                  Relative names will be resolved from the
     *                                  same theme that the calling view belongs
     *                                  to.
     *
     * @return null|ThemeResource|void  ThemeResource containing the path to
     *                                  resource file or directory.
     */
    public function resolve(string $viewName)
    {
        if (empty($viewName)) {
            return;
        }

        $parser = new ViewNameParser($viewName);

        // Absolute name, e.g. miravel::theme.elements.elementname
        if ($parser->isMiravelNamespacedView()) {
            return $this->resolveFromTheme(
                $parser->getTheme(),
                $parser->getName()
            );
        }

        // Relative name
        $parts = $parser->getParts();

        // 'elementname' or 'layoutname' or 'templatename'
        if (count($parts) == 1) {
            // if this is an element called by another element, try resolving it
            // from the parent element's theme
            if ($resource = $this->tryResolveElementFromTopLevel($parts[0])) {
                return $resource;
            }

            return $this->resolveFromCurrentTheme($parts[0]);
        }

        // 'themename.elementname' or 'themename.layoutname'
        if (count($parts) == 2) {
            return $this->resolveFromTheme($parts[0], $parts[1]);
        }

        // 'theme.elements.elementname[.whatever.else]'
        // 'theme.layouts.layoutname[.whatever.else]'
        return $this->resolveFromViewParts($parts);
    }

    /**
     * @param string $themeName
     * @param string $resourceName
     *
     * @return null|ThemeResource|void
     */
    protected function resolveFromTheme(string $themeName, string $resourceName)
    {
        $theme = MiravelFacade::makeTheme($themeName);
        if ($theme->exists()) {
            $resourceName = $this->getTypeAwareName($resourceName);

            return $theme->getResource($resourceName);
        }
    }

    /**
     * @param string $resourceName
     *
     * @return null|ThemeResource|void
     */
    protected function resolveFromCurrentTheme(string $resourceName)
    {
        $theme = MiravelFacade::getCurrentViewParentTheme();

        if ($theme) {
            $resourceName = $this->getTypeAwareName($resourceName);

            return $theme->getResource($resourceName);
        }
    }

    /**
     * @param array $parts
     *
     * @return null|ThemeResource|void
     */
    protected function resolveFromViewParts(array $parts)
    {
        $themeName = $parts[0];
        $viewName  = implode('.', array_slice($parts, 1));
        $theme     = MiravelFacade::makeTheme($themeName);

        if ($theme->exists()) {
            return $theme->getResource($viewName);
        }
    }

    /**
     * 'elementname' becomes 'elements.elementname'. This makes it possible to
     * pass it to theme class for lookup.
     *
     * @param $name    the resource name to prepend with type.
     *
     * @return string  the converted name.
     */
    protected function getTypeAwareName($name)
    {
        return sprintf('%s.%s', $this->viewType, $name);
    }

    protected function tryResolveElementFromTopLevel($name)
    {
        if ('elements' !== $this->viewType) {
            return;
        }

        if (!$topLevel = MiravelFacade::getTopLevelRenderingElement()) {
            return;
        }

        if (!$themeName = substr($topLevel, 0, strpos($topLevel, '.'))) {
            return;
        }

        return $this->resolveFromTheme($themeName, $name);
    }
}
