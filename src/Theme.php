<?php

namespace Miravel;

use Miravel\Facade as MiravelFacade;
use Miravel\Factories\ThemeFactory;

/**
 * Class Theme
 *
 * A class representing a Miravel theme.
 *
 * @package Miravel
 */
class Theme
{
    // If the theme has configuration values, they will be searched in this file
    // inside theme directory.
    const CONFIG_FILE_NAME = 'config.php';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $paths  = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Theme
     */
    protected $parentTheme;


    /**
     * Theme constructor.
     *
     * @param $name  the theme name.
     */
    public function __construct($name)
    {
        $this->name = $name;

        $this->initPaths();

        $this->initConfig();

        $this->register();

        $this->initParentTheme();
    }

    /**
     * @return void
     */
    protected function initPaths()
    {
        $paths = Utilities::getResourceLookupPaths();

        foreach ($paths as $pathname => $path) {
            $path = implode(DIRECTORY_SEPARATOR, [$path, $this->name]);
            if (is_dir($path)) {
                $this->paths[$pathname] = $path;
            }
        }
    }

    /**
     * @return void
     */
    protected function initConfig()
    {
        $config = [];

        $vendorConfig = $this->getConfigFromPath('vendor');
        $appConfig    = $this->getConfigFromPath('app');

        if (!empty($vendorConfig)) {
            $config = $vendorConfig;
        }

        if (!empty($appConfig)) {
            $config = array_replace_recursive($config, $appConfig);
        }

        $this->config = $config;
    }
    
    /**
     * @param string $pathname
     *
     * @return array
     */
    protected function getConfigFromPath(string $pathname): array
    {
        $configFile = $this->buildFilePath(static::CONFIG_FILE_NAME, $pathname);

        return ($configFile && file_exists($configFile)) ?
            (array)include($configFile) :
            [];
    }


    /**
     * Initialize the parent theme.
     *
     * @return void
     */
    public function initParentTheme()
    {
        if (
            isset($this->config['extends']) &&
            is_string($this->config['extends'])
        ) {
            $parentTheme = MiravelFacade::makeTheme($this->config['extends']);
            $this->setParentTheme($parentTheme);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Theme
     */
    public function setName(string $name): Theme
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     *
     * @return Theme
     */
    public function setPaths(array $paths): Theme
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return Theme
     */
    public function setConfig(array $config): Theme
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return null|Theme
     */
    public function getParentTheme()
    {
        return $this->parentTheme;
    }

    /**
     * @param null|Theme $parentTheme
     *
     * @return Theme $this
     */
    public function setParentTheme(Theme $parentTheme = null): Theme
    {
        $this->parentTheme = $parentTheme;

        return $this;
    }

    /**
     * Check if at least one source directory exists for this theme
     *
     * @return bool
     */
    public function exists(): bool
    {
        return !empty($this->paths);
    }


    /**
     * @param string $filename
     * @param string $path
     *
     * @return null|string
     */
    protected function buildFilePath(string $filename, string $path = 'vendor')
    {
        if (isset($this->paths[$path])) {
            return implode(DIRECTORY_SEPARATOR, [
                $this->paths[$path],
                $filename
            ]);
        }
    }

    /**
     * Given a relative resource name (e.g. layout or element name, or a static
     * asset) return its full absolute path, or null if not found.
     *
     * The lookup is done in this theme and its parent hierarchy, if any.
     *
     * @param string $name            a file path e.g. 'layouts/basic/style.css' OR
     *                                a template definition e.g. 'elements.grid'
     * @param array $processedThemes  global list of themes that have already
     *                                been processed during current lookup
     *
     * @return null|ThemeResource     ThemeResource object containing the path
     *                                to the file or directory
     */
    public function getResource(string $name, array $processedThemes = [])
    {
        // global list is used to prevent infinite loop on circular reference
        // e.g. when 2 themes extend each other
        if (in_array($this->name, $processedThemes)) {
            return;
        }

        // look in this theme
        $resource = $this->lookupResource($name);

        // look in parent themes, if any
        if (!$resource && $this->parentTheme) {
            $processedThemes[] = $this->name;
            $resource = $this->parentTheme->getResource($name, $processedThemes);
        }

        if ($resource) {
            $resource->setTheme($this);
        }

        return $resource;
    }

    /**
     * Same as getResource(), but if the found path is a directory, it will look
     * for the template file in that directory; if missing, will continue search
     * in parent themes.
     *
     * @param string $name            the resource name to resolve.
     * @param array $processedThemes  an array of theme names that have already
     *                                been processed in the current cycle, to
     *                                avoid infinite loop.
     *
     * @return null|string            absolute path to the view file, or null if
     *                                such a file is not found.
     */
    public function getViewFile(
        string $name,
        array $processedThemes = []
    ) {
        // global list is used to prevent infinite loop on circular reference
        // e.g. when 2 themes extend each other
        if (in_array($this->name, $processedThemes)) {
            return;
        }

        if ($resource = $this->getResource($name)) {
            return $resource->getViewFile();
        }
    }

    /**
     * @param string $name
     *
     * @return string|void
     */
    protected function lookupResource(string $name)
    {
        if (empty($name)) {
            return;
        }
        
        // assume a static / source file first
        if ($path = $this->lookupFile($name)) {
            return new ThemeResource($path);
        }

        $relative = Utilities::viewNameDotsToSlashes($name);

        // see if requested path is a directory
        if ($path = $this->lookupDirectory($relative)) {
            return new ThemeResource($path);
        }

        // see if requested path is a template with one of configured template
        // extensions
        if ($path = $this->lookupTemplateFile($relative)) {
            return new ThemeResource($path);
        }
    }

    /**
     * @param string $relativePath
     *
     * @return string|void
     */
    protected function lookupFile(string $relativePath)
    {
        foreach ($this->paths as $pathname => $path) {
            $fullpath = implode(DIRECTORY_SEPARATOR, [$path, $relativePath]);
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }
    }

    /**
     * @param string $relativePath
     *
     * @return string|void
     */
    protected function lookupTemplateFile(string $relativePath)
    {
        $extensions = (array)MiravelFacade::getConfig('template_file_extensions');

        foreach ($extensions as $extension) {
            $completePath = "$relativePath.$extension";
            if ($match = $this->lookupFile($completePath)) {
                return $match;
            }
        }
    }

    /**
     * @param string $relativePath
     *
     * @return string|void
     */
    protected function lookupDirectory(string $relativePath)
    {
        foreach ($this->paths as $pathname => $path) {
            $fullpath = implode(DIRECTORY_SEPARATOR, [$path, $relativePath]);
            if (is_dir($fullpath)) {
                return $fullpath;
            }
        }
    }

    /**
     * Register the theme on the global register
     */
    protected function register()
    {
        ThemeFactory::register($this->getName(), $this);
    }
    
    /**
     * Get Type resurse
     */
    protected function getTypeResurceByName($name){
        $Data = explode('.', $name);
        return !empty($Data) ? current($Data) : null;
    }
}
