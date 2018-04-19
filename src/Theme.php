<?php

namespace Miravel;

use Symfony\Component\Filesystem\Filesystem;
use Miravel\Resources\BaseThemeResource;
use Miravel\Factories\ResourceFactory;
use Miravel\Factories\ElementFactory;
use Miravel\Facade as MiravelFacade;
use Symfony\Component\Finder\Finder;
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

        $this->register();

        $this->initParentTheme();
    }

    /**
     * Register the theme on the global register
     */
    protected function register()
    {
        ThemeFactory::register($this->getName(), $this);
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
     * - in the vendor directory (vendor/miravel/miravel/resources/themes)
     *
     * First, a theme file is sought in the app scope and if missing,
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

            // make sure app is always first
            ksort($result);

            static::$themeDirPaths = $result;
        }

        return static::$themeDirPaths;
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
                rtrim($this->paths[$path], '\/'),
                ltrim($filename, '\/')
            ]);
        }
    }

    /**
     * Given a relative resource name (e.g. layout or element name, or a static
     * asset) return the resource, or null if not found.
     *
     * The lookup is done in this theme and its parent hierarchy, if any.
     *
     * @param string $name                  A file path e.g. 'layouts/basic/style.css'
     *                                      OR a dot definition e.g. 'elements.grid'
     * @param array $extensions             An array of file extensions to try
     * @param bool $ancestry                Whether to search in parent themes
     * @param array $processedThemes        Static list of themes that have already
     *                                      been processed during this call
     *
     * @return null|BaseThemeResource|void  Resource object containing the path
     *                                      to the file or directory
     */
    public function getResource(
        string $name,
        array $extensions = [],
        bool $ancestry = true,
        array $processedThemes = []
    ) {
        // global list is used to prevent infinite loop on circular reference
        // e.g. when 2 themes extend each other
        if (in_array($this->name, $processedThemes)) {
            return;
        }

        if ($path = $this->lookupResource($name, $extensions)) {
            // found in this theme
            return $this->makeResource($path);
        }

        if (!$ancestry) {
            // explicit prohibition to look up parents
            return;
        }

        if ($resource = $this->findResourceInThemeHierarchy(
            $name,
            $extensions,
            $processedThemes
        )) {
            $resource->setCallingTheme($this);

            return $resource;
        }
    }

    /**
     * Look for resource in this theme; failing that, delegate the search to
     * parent themes.
     *
     * @param string $name
     * @param array $extensions
     * @param array $processedThemes
     *
     * @return BaseThemeResource|void
     */
    protected function findResourceInThemeHierarchy(
        string $name,
        array $extensions,
        array &$processedThemes
    ) {
         if ($this->parentTheme) {

            // look in parent themes, if any
            $processedThemes[] = $this->name;
            return $this->parentTheme->getResource(
                $name,
                $extensions,
                $processedThemes
            );
        }
    }

    /**
     * Same as getResource(), but if the found path is a directory, it will look
     * for the template file in that directory; if missing, will continue search
     * in parent themes.
     *
     * @param string $name            The resource name to resolve.
     * @param bool $ancestry          Whether to look in parent themes
     * @param array $processedThemes  An array of theme names that have already
     *                                been processed in the current cycle, to
     *                                avoid infinite loop.
     *
     * @return null|string            Absolute path to the view file, or null if
     *                                such a file is not found.
     */
    public function getViewFile(
        string $name,
        bool $ancestry = true,
        array $processedThemes = []
    ) {
        // global list is used to prevent infinite loop on circular reference
        // e.g. when 2 themes extend each other
        if (in_array($this->name, $processedThemes)) {
            return;
        }

        if ($resource = $this->getResource($name, [], $ancestry)) {
            return $resource->getViewFile();
        }
    }

    /**
     * Given resource name, return the filesystem path
     *
     * @param string $name
     *
     * @param array $extensions
     *
     * @return string|void
     */
    protected function lookupResource(string $name, array $extensions = [])
    {
        if (empty($name)) {
            return;
        }

        // assume literal path first
        if ($path = $this->lookupPath($name, $extensions)) {
            return $path;
        }

        // then try to expand dot notation
        $expanded = Utilities::dotsToSlashes($name);

        if ($path = $this->lookupPath($expanded, $extensions)) {
            return $path;
        }
    }

    /**
     * @param string $relativePath
     *
     * @param array $extensions
     *
     * @return string|void
     */
    protected function lookupPath(string $relativePath, array $extensions = [])
    {
        foreach ($this->paths as $pathname => $path) {
            $fullpath = implode(DIRECTORY_SEPARATOR, [$path, $relativePath]);

            if (empty($extensions) && file_exists($fullpath)) {
                return $fullpath;
            }

            // try the extensions
            $base = $fullpath;

            if (!empty($extensions)) {
                foreach ($extensions as $ext) {
                    $fullpath = "$base.$ext";
                    if (file_exists($fullpath)) {
                        return $fullpath;
                    }
                }
            }
        }
    }

    public function makeResource(string $path)
    {
        $resource = ResourceFactory::make($path, $this);

        return $resource;
    }

    public function getElement(string $name, $data = [], array $options = [])
    {
        if (!$resource = $this->getResource("elements.$name")) {
            return;
        }

        return ElementFactory::makeFromResource(
            $name,
            $resource,
            $data,
            $options
        );
    }

    /**
     * Get the flat list of relative paths of all directory resources within the
     * theme folder (including parent themes).
     *
     * @param null $subset    Restrict to certain subdirectories, e.g. 'elements'
     *                        or ['elements', 'layouts']
     * @param bool $ancestry  Whether to search in parent themes
     *
     * @return array          An array with relative paths
     */
    public function getDirectoryResources($subset = null, bool $ancestry = true)
    {
        $paths = $resources = [];

        $subset = $subset ? (array)$subset : static::getDefaultSubset();

        foreach ($subset as $relativePath) {
            $relativePath = Utilities::dotsToSlashes($relativePath);
            $paths += $this->scan($relativePath, $ancestry);
        }

        foreach ($paths as $relativePath) {
            $resources[] = $this->getResource($relativePath);
        }

        return $resources;
    }

    protected static function getDefaultSubset(): array
    {
        return ['elements', 'layouts', 'templates', 'skins'];
    }

    public function scan($relativePath, bool $ancestry = true)
    {
        $results = [];
        $fs = new Filesystem;

        foreach ($this->getPaths() as $type => $path) {
            $fullpath = $this->buildFilePath($relativePath, $type);
            $finder   = $this->getFinder();
            $finder->in($fullpath);
            $objects = $finder->directories();

            foreach ($objects as $object) {
                $result = $object->getPathname;
                $result = $fs->makePathRelative($result, $path);
                $results[] = $result;
            }
        }

        if ($ancestry && $this->parentTheme) {
            $results += $this->parentTheme->scan($relativePath);
        }

        return array_unique($results);
    }

    protected function getFinder()
    {
        return new Finder;
    }
}
