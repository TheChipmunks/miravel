<?php

namespace Miravel;

use Miravel\Exceptions\CurrentThemeException;
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
    /**
     * @var string
     */
    protected $themeName = '';

    /**
         * @var string
         */
    protected $resourceType = '';

    /**
         * @var string
         */
    protected $resourceName = '';

    /**
     * ViewNameResolver constructor.
     *
     * @param string $resourceType  may be 'elements', 'layouts', or 'templates'
     * @param string $themeName     theme name, if known (optional)
     */
    public function __construct(string $resourceType, string $themeName = '')
    {
        $this->setResourceType($resourceType);

        if (!empty($themeName)) {
            $this->setThemeName($themeName);
        }
    }

    /**
     * Resolve an element view.
     *
     * @param string $resourceName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveElement(string $resourceName)
    {
        $resolver = new static('elements');

        return $resolver->resolve($resourceName);
    }

    /**
     * Resolve a layout view.
     *
     * @param string $resourceName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveLayout(string $resourceName)
    {
        $resolver = new static('layouts');

        return $resolver->resolve($resourceName);
    }

    /**
     * Resolve a template view.
     *
     * @param string $resourceName
     *
     * @return null|string|void  the resolved directory or file path.
     */
    public static function resolveTemplate(string $resourceName)
    {
        $resolver = new static('templates');

        return $resolver->resolve($resourceName);
    }

    /**
     * Translates view names to file or directory paths. File path is the actual
     * view path, while directory is the one containing the template file.
     *
     * @param string $rawResourceName       the absolute name (e.g.
     *                                      "miravel::theme.elements.name",
     *                                      "theme.elements.name",
     *                                      "theme.elementname")
     *                                      or relative name (like "elementname").
     *                                      Relative names will be resolved from the
     *                                      same theme that the calling view belongs
     *                                      to.
     *
     * @return null|BaseThemeResource|void  Theme resource containing the path to
     *                                      resource file or directory.
     */
    public function resolve(string $rawResourceName)
    {
        if (empty($rawResourceName)) {
            return;
        }

        // initialize $this->themeName and $this->resourceName
        $this->initNameComponents($rawResourceName);

        if (!$theme = MiravelFacade::makeAndValidateTheme($this->themeName)) {
            MiravelFacade::exception(CurrentThemeException::class, [], __FILE__, __LINE__);
        }

        $relativeResourceName = $this->getNameRelativeToTheme();

        return $theme->getResource($relativeResourceName);
    }

    /**
     * Make sure we have all three parts:
     * - theme name
     * - resource type
     * - resource name
     *
     * @param string $rawResourceName
     */
    public function initNameComponents(string $rawResourceName)
    {
        $parser = new ViewNameParser($rawResourceName);

        if ($parser->isMiravelNamespacedView()) {
            // we've got a fully qualified path to a resource, e.g.
            // miravel::theme.elements.elementname
            $this->setThemeName($parser->getTheme());
            $this->setResourceName($parser->getName());
        } else {
            // we've got a partial name, attempt to restore components
            $nameParts = $parser->getParts();
            $count = count($nameParts);
            switch ($count) {
                case 1:
                    // ask Miravel for current theme name
                    $this->setThemeName($this->getCurrentTheme());
                    $this->setResourceName($nameParts[0]);
                    break;
                case 2:
                    $this->setThemeName($nameParts[0]);
                    $this->setResourceName($nameParts[1]);
                    break;
                default:
                    $this->setThemeName($parser->getTheme());
                    $this->setResourceType($parser->getType());
                    $this->setResourceName($parser->getName());
            }
        }
    }

    /**
     * Try to define the current theme (the theme that has initiated the current
     * render).
     *
     * @return mixed
     */
    protected function getCurrentTheme()
    {
        if (!$currentTheme = MiravelFacade::getCurrentTheme()) {
            MiravelFacade::exception(CurrentThemeException::class, [], __FILE__, __LINE__);
        }

        return $currentTheme->getName();
    }

    /**
     * Get the full name but omitting the theme name, e.g.
     * elements.elementname
     *
     * @return string
     */
    public function getNameRelativeToTheme()
    {
        return implode('.', [
            $this->getResourceType(),
            $this->getResourceName()
        ]);
    }

    /**
         * @return string
         */
    public function getThemeName(): string
    {
        return $this->themeName;
    }

    /**
     * @param string $themeName
     */
    public function setThemeName(string $themeName)
    {
        $this->themeName = $themeName;
    }

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * @param string $resourceType
     */
    public function setResourceType(string $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @return string
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     */
    public function setResourceName(string $resourceName)
    {
        $this->resourceName = $resourceName;
    }
}
