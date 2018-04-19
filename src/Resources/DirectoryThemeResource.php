<?php

namespace Miravel\Resources;

use Miravel\Facade as MiravelFacade;

class DirectoryThemeResource extends BaseThemeResource
{
    /**
     * @return string|void
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
     * @return string|void
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

    /**
     * @return string|void
     */
    public function getCssSourceFile()
    {
        $userPreferredSources = static::getUserPreferredCssSources();
        $otherSources         = static::getRemainingCssSources();

        $relativePath = $this->getRelativePathCssSource();

        foreach ([$userPreferredSources, $otherSources] as $sourceSet) {
            if ($resource = $this->getCallingTheme()->getResource(
                $relativePath,
                $sourceSet
            )) {
                return $resource->getRealPath();
            };
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

    public function getRelativePathCssSource()
    {
        $relativePath  = $this->getRelativePath();
        $styleFileName = MiravelFacade::getConfig('style_file_name');

        return implode(DIRECTORY_SEPARATOR, [
            $relativePath,
            $styleFileName
        ]);
    }


}
