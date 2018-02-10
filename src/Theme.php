<?php

namespace Miravel;

use Miravel;

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

    // Cached paths where files related to this theme will be searched, in order
    // or appearance on the list.
    protected static $themeDirPaths;

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
    }

    /**
     * @return void
     */
    protected function initPaths()
    {
        $paths = static::getThemeDirPaths();

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
     * Get the directories where the files belonging to this theme might be
     * located. Normally, there are two paths:
     * - in the application (resources/views/vendor/miravel)
     * - in the vendor directory (vendor/miravel/resources/themes)
     *
     * First, a theme file is sought in the application directory and if missing,
     * in the vendor directory.
     *
     * This function returns the paths without the theme name appended yet.
     *
     * @return array
     */
    public static function getThemeDirPaths(): array
    {
        if (is_null(static::$themeDirPaths)) {
            $hints  = app()->make('view.finder')->getHints();
            $result = [];

            if (isset($hints['miravel']) && is_array($hints['miravel'])) {
                foreach ($hints['miravel'] as $hint) {
                    $realpath = realpath($hint);

                    if (Utilities::isResourceViewPath($realpath)) {
                        $result['app'] = $realpath;
                    } elseif (Utilities::isVendorPackagePath($realpath)) {
                        $result['vendor'] = $realpath;
                    }
                }
            }

            ksort($result);

            static::$themeDirPaths = $result;
        }

        return static::$themeDirPaths;
    }

    /**
     * If a view path contains a theme name, return it.
     *
     * @param string $viewPath  the path to the view file or directory.
     *
     * @return string|void
     */
    public static function getThemeNameFromViewPath(string $viewPath)
    {
        // TODO: move to Utilities

        $lookupPaths = static::getThemeDirPaths();

        foreach ($lookupPaths as $lookupPath) {
            $lookupPath = realpath($lookupPath);
            if (0 !== strpos($viewPath, $lookupPath)) {
                continue;
            }

            $relative = substr($viewPath, strlen($lookupPath));
            $relative = trim($relative, DIRECTORY_SEPARATOR);
            $segments = explode(DIRECTORY_SEPARATOR, $relative);
            if (count($segments) <= 1) {
                continue;
            }

            return $segments[0];
        }
    }

    /**
     * Initialize the parent theme.
     *
     * @return void
     */
    public function initParentTheme()
    {
        if (
            isset($this->config['extend']) &&
            is_string($this->config['extend'])
        ) {
            $parentTheme = Miravel::makeTheme($this->config['extend']);
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
        $this->mergeParentThemeConfig();

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
     * Only missing values from parent config will be appended.
     * Otherwise, current (child) theme config values take precedence.
     *
     * @return void
     */
    protected function mergeParentThemeConfig()
    {
        if (!$this->parentTheme) {
            return;
        }

        $parentConfig = $this->parentTheme->getConfig();

        $this->config += $parentConfig;
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
        $extensions = (array)Miravel::getConfig('template_file_extensions');

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
}
