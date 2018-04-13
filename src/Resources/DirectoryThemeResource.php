<?php

namespace Miravel\Resources;

use Miravel\Facade as MiravelFacade;

class DirectoryThemeResource extends BaseThemeResource
{
    /**
     * @return FileThemeResource|void
     */
    public function getClassFile()
    {
        $relativePath = $this->getRelativePathClass();

        $resource = $this->getCallingTheme()->getResource($relativePath);

        if ($resource) {
            return $resource->getRealPath();
        }
    }

    /**
         * @return FileThemeResource|void
         */
    public function getViewFile()
    {
        $relativePath = $this->getRelativePathView();
        $extensions   = MiravelFacade::getConfig('template_file_extensions');

        $resource = $this->getCallingTheme()->getResource(
            $relativePath,
            $extensions
        );

        if ($resource) {
            return $resource->getRealPath();
        }
    }

    public function getRelativePathView()
    {
        $relativePath = $this->getRelativePath();
        $viewFileName = MiravelFacade::getConfig('template_file_name');

        return implode(DIRECTORY_SEPARATOR, [
            $relativePath,
            $viewFileName
        ]);
    }

    public function getRelativePathClass()
    {
        $relativePath  = $this->getRelativePath();
        $classFileName = MiravelFacade::getConfig('class_file_name');

        return implode(DIRECTORY_SEPARATOR, [
            $relativePath,
            $classFileName
        ]);
    }
}
