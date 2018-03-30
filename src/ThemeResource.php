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

        if ($this->isDir()) {
            return Utilities::findTemplateInDirectory($this->getPathname());
        }
    }

    /**
     * @param string $classFileName
     *
     * @return string|void
     */
    public function getClassFile(string $classFileName)
    {
        if (!$this->isDir()) {
            return;
        }

        $path = implode(DIRECTORY_SEPARATOR, [$this->getPathname(), $classFileName]);

        if (!file_exists($path)) {
            return;
        }

        return $path;
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
