<?php

namespace Miravel;

use Miravel\Resources\BaseThemeResource;

/**
 * Class Layout
 *
 * The class that represents a Miravel layout.
 *
 * @package Miravel
 */
class Layout
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var BaseThemeResource
     */
    protected $resource;

    /**
     * Layout constructor.
     *
     * @param BaseThemeResource $resource
     */
    public function __construct(BaseThemeResource $resource)
    {
        $this->setResource($resource);
    }

    /**
     * See if the layout was instantiated against a valid template file.
     *
     * @return bool
     */
    public function exists()
    {
        return is_file($this->path);
    }

    /**
     * Get this layout's theme.
     *
     * @return Theme
     */
    public function getTheme(): Theme
    {
        return $this->theme;
    }

    /**
     * Set this layout's theme.
     *
     * @param Theme $theme
     *
     * @return Layout
     */
    protected function setTheme(Theme $theme): Layout
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get this layout's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set this layout's name.
     *
     * @param string $name
     *
     * @return Layout
     */
    public function setName(string $name): Layout
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get this layout's path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set this layout's path.
     *
     * @param string $path
     *
     * @return Layout
     */
    public function setPath(string $path): Layout
    {
        $this->path = $path;

        return $this;
    }

    public function setResource(BaseThemeResource $resource)
    {
        $this->resource = $resource;

        $path = $resource->getViewFile();

        // set the path to the template file
        if ($path) {
            $this->setPath($path);
        }

        // set the name
        if ($resource->isDir()) {
            $this->setName($resource->getBasename());
        } elseif ($path) {
            $this->setName(Utilities::getTemplateBaseName($path));
        }

        // set the theme
        if ($path && ($theme = Utilities::pathBelongsToTheme($this->path))) {
            $this->setTheme($theme);
        }
    }
}
