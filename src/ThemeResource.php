<?php

namespace Miravel;

use Miravel\Facade as MiravelFacade;
use SplFileInfo;

/**
 * Class ThemeResource
 *
 * A class that represents a theme resource that may be represented with a
 * single file or a folder with files.
 *
 * @package Miravel
 */
class ThemeResource extends SplFileInfo
{
    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @return bool
     */
    public function isViewResource()
    {
        if (!$this->isFile()) {
            return false;
        }

        $regex    = $this->getTemplateFilenameRegex();
        $filename = $this->getFilename();

        return (bool)preg_match($regex, $filename);
    }

    /**
     * @return string|void
     */
    public function getViewFile()
    {
        if ($this->isViewResource()) {
            return $this->getRealPath();
        }

        if (!$this->isDir()) {
            return;
        }

        if (!$relativePath = $this->getRelativeViewPath()) {
            return;
        }

        if (!$resource = $this->getTheme()->getResource($relativePath)) {
            return;
        }

        return $resource->getRealPath();
    }

    /**
     * @param string $classFileName
     *
     * @return string|void
     */
    public function getClassFile(string $classFileName)
    {
        if (!$this->isDir()) {
            // this resource is represented by a single file. If the file name
            // is the same as the requested class file name, just return the
            // full path to it.
            $filename = $this->getFilename();

            if ($filename == $classFileName) {
                return $this->getRealPath();
            }
        } else {
            // this resource is represented by a directory. Try to find the
            // requested class filename in it. We do it by asking the theme to
            // look up the entire hierarchy tree.
            if (!$relativePath = $this->getRelativeClassPath($classFileName)) {
                return;
            }

            if (!$resource = $this->getTheme()->getResource($relativePath)) {
                return;
            }

            return $resource->getRealPath();
        }
    }

    /**
     * Get resource path relative to its parent theme.
     *
     * @return mixed
     */
    protected function getRelativePath()
    {
        // TODO: move to Utilities

        $lookupPaths  = Theme::getThemeDirPaths();
        $resourcePath = $this->getRealPath();

        foreach ($lookupPaths as $lookupPath) {
            $lookupPath = realpath($lookupPath);
            if (0 !== strpos($resourcePath, $lookupPath)) {
                continue;
            }

            $relative = substr($resourcePath, strlen($lookupPath));
            $relative = trim($relative, DIRECTORY_SEPARATOR);

            // shift out the theme name
            $segments = explode(DIRECTORY_SEPARATOR, $relative, 2);
            if (count($segments) <= 1) {
                continue;
            }

            return $segments[1];
        }
    }

    /**
     * Get the location where to the view file is supposed to be.
     *
     * Miravel will start searching for the view file in this location and then
     * move up the theme hierarchy tree.
     *
     * @return mixed
     */
    public function getRelativeViewPath()
    {
        $relative = $this->getRelativePath();

        if ($this->isViewResource()) {
            return $relative;
        }

        if ($this->isDir()) {
            $viewFileName = MiravelFacade::getConfig('template_file_name');

            return implode(DIRECTORY_SEPARATOR, [$relative, $viewFileName]);
        }
    }

    protected function getRelativeClassPath(string $classFileName)
    {
        $relative = $this->getRelativePath();

        if (!$this->isDir() && $this->getFilename() == $classFileName) {
            return $relative;
        }

        if ($this->isDir()) {
            return implode(DIRECTORY_SEPARATOR, [$relative, $classFileName]);
        }
    }

    /**
     * @return string
     */
    protected function getTemplateFilenameRegex()
    {
        $extensions = (array)MiravelFacade::getConfig('template_file_extensions');
        $extensions = array_map(
            function (string $item) {
                return '\.' . preg_quote($item, '/');
            },
            $extensions
        );

        $regex = sprintf('/(%s)$/', implode('|', $extensions));

        return $regex;
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
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;
    }
}
