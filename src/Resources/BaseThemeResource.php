<?php

namespace Miravel\Resources;

use Miravel\Exceptions\PathThemeAttributionException;
use Miravel\Facade as MiravelFacade;
use Miravel\Utilities;
use Miravel\Theme;
use SplFileInfo;

abstract class BaseThemeResource extends SplFileInfo
{
    /**
     * @var Theme
     */
    protected $theme;

    /**
         * @var Theme
         */
    protected $callingTheme;

    /**
     * @var string
     */
    protected $relativePath;

    /**
         * @var string
         */
    protected $type;

    public function __construct($filepath)
    {
        parent::__construct($filepath);

        $this->initTheme();
    }

    /**
     * Analyze the path and extract theme information. Set the theme object that
     * represents the theme holding this resource, and the relative path to the
     * resource inside the theme.
     */
    protected function initTheme()
    {
        $path = $this->getRealPath();

        $components = Utilities::getPathComponents($path);

        if (
            !is_array($components) ||
            !isset($components['theme']) ||
            !$theme = MiravelFacade::makeAndValidateTheme($components['theme'])
        ) {
            MiravelFacade::exception(PathThemeAttributionException::class, ['path' => $path], __FILE__, __LINE__);
        }

        $this->setTheme($theme);
        $this->setCallingTheme($theme);
        $this->setRelativePath($components['relative']);
    }

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * @param string $relativePath
     */
    protected function setRelativePath(string $relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * @return Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param Theme $theme
     */
    protected function setTheme(Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * @param Theme $theme
     */
    public function setCallingTheme(Theme $theme)
    {
        $this->callingTheme = $theme;
    }

    /**
     * @return Theme|void
     */
    public function getCallingTheme()
    {
        return $this->callingTheme;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    protected function setType(string $type)
    {
        $this->type = $type;
    }

    abstract public function getViewFile();

    abstract public function getClassFile();
}
