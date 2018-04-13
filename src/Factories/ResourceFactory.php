<?php

namespace Miravel\Factories;

use Miravel\Exceptions\PathNotFoundException;
use Miravel\Exceptions\UnknownResourceFstypeException;
use Miravel\Facade as MiravelFacade;
use Miravel\Resources\DirectoryThemeResource;
use Miravel\Resources\FileThemeResource;

class ResourceFactory
{
    const FSTYPE_FILE      = 'file';
    const FSTYPE_DIRECTORY = 'directory';

    public static function make(string $path)
    {
        $path = realpath($path);

        if (!file_exists($path)) {
            MiravelFacade::exception(PathNotFoundException::class, ['path' => $path], __FILE__, __LINE__);
        }

        $fstype = static::getFsType($path);

        switch ($fstype) {
            case static::FSTYPE_FILE:
                return static::makeFileResource($path);
            case static::FSTYPE_DIRECTORY:
                return static::makeDirectoryResource($path);
        }
    }

    public static function getFsType($path)
    {
        if (is_file($path)) {
            return static::FSTYPE_FILE;
        }

        if (is_dir($path)) {
            return static::FSTYPE_DIRECTORY;
        }

        MiravelFacade::exception(UnknownResourceFstypeException::class, ['path' => $path], __FILE__, __LINE__);
    }

    protected static function makeFileResource(string $path)
    {
        return new FileThemeResource($path);
    }

    protected static function makeDirectoryResource(string $path)
    {
        return new DirectoryThemeResource($path);
    }
}
