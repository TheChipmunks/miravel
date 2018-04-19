<?php

namespace Miravel\Resources;

use Miravel\Facade as MiravelFacade;
use Miravel\Utilities;

class FileThemeResource extends BaseThemeResource
{
    public function getViewFile()
    {
        if ($this->isViewFile()) {
            return $this->getRealPath();
        }
    }

    public function getClassFile()
    {
        if ($this->isClassFile()) {
            return $this->getRealPath();
        }
    }


    public function getCssSourceFile()
    {
        if ($this->isCssSourceFile()) {
            return $this->getRealPath();
        }
    }

    public function isViewFile()
    {
        $basename = $this->getBasename();

        $name = MiravelFacade::getConfig('template_file_name');
        $exts = MiravelFacade::getConfig('template_file_extensions');

        foreach ($exts as $ext) {
            $pattern = "$name.$ext";
            if (Utilities::FileNameCmp($basename, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function isClassFile()
    {
        $basename = $this->getBasename();
        $classFileName = MiravelFacade::getConfig('class_file_name');

        return Utilities::FileNameCmp($basename, $classFileName);
    }

    public function isCssSourceFile()
    {
        $ext = $this->getExtension();

        // $possibleExts = MiravelFacade::getConfig('css_source_extensions');
        $possibleExts = static::getPossibleCssSources();

        foreach ($possibleExts as $possibleExt) {
            if (Utilities::mbStrCmp($ext, $possibleExt)) {
                return true;
            }
        }

        return false;
    }
}
