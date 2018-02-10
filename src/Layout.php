<?php

namespace Miravel;

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
     * Layout constructor.
     *
     * @param string $path  the path to the layout template
     */
    public function __construct(string $path)
    {
        $templateFile = is_dir($path) ?
            Utilities::findTemplateInDirectory($path) :
            $path;

        $this->setPath($templateFile);

        $this->setName(Utilities::getTemplateBaseName($templateFile));

        if ($theme = Utilities::pathBelongsToTheme($templateFile)) {
            $this->setTheme($theme);
        }
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
}
