<?php

namespace Miravel;

use Miravel\Builders\LaravelMixThemeBuilder;
use Miravel\Builders\ThemeBuilderInterface;
use Miravel\Exceptions\PathPurgeSafeCheckException;
use Symfony\Component\Filesystem\Filesystem;
use Miravel\Exceptions\PathPurgeException;
use Miravel\Resources\BaseThemeResource;
use Miravel\Factories\ResourceFactory;
use Miravel\Factories\ElementFactory;
use Miravel\Facade as MiravelFacade;
use Symfony\Component\Finder\Finder;
use Miravel\Factories\ThemeFactory;
use Exception;

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

    const RESOURCE_FILTER_DIRECTORIES = 'dirs';
    const RESOURCE_FILTER_FILES       = 'files';
    const RESOURCE_FILTER_ALL         = 'all';

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
            $paths = [
                'app'    => realpath(MiravelFacade::getConfig('paths.app')),
                'vendor' => realpath(MiravelFacade::getConfig('paths.vendor')),
            ];

            static::$themeDirPaths = $paths;
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
                true,
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
     * Get the flat list of all matching resources within the theme folder
     * (including parent themes).
     *
     * @param null $subset                   Restrict to certain subdirectories, e.g. 'elements'
     *                                       or ['elements', 'layouts']
     * @param string $filter                 Whether to include file resources, directory resources,
     *                                       or both
     * @param bool $ancestry                 Whether to search in parent themes
     *
     * @param callable|null $finderModifier
     *
     * @return array                         An array with resource objects
     */
    public function getResourceList(
        $subset = null,
        $filter = self::RESOURCE_FILTER_ALL,
        bool $ancestry = true,
        callable $finderModifier = null
    ) {
        $paths = $resources = [];

        $subset = $subset ? (array)$subset : ['']; // look, robot face!

        foreach ($subset as $relativePath) {
            $relativePath = Utilities::dotsToSlashes($relativePath);
            $paths = array_merge(
                $paths,
                $this->scan($relativePath, $filter, $ancestry, $finderModifier)
            );
        }

        $paths = array_unique($paths);

        foreach ($paths as $relativePath) {
            $resources[] = $this->getResource($relativePath);
        }

        return $resources;
    }

    /**
     * Get the flat list of relative paths matching the given criteria
     * (including parent themes).
     *
     * @param string $relativePath           The relative path that is the subtree root
     *                                       to start searching in.
     * @param string $filter                 Whether to pick files, directories, or both
     * @param bool $ancestry                 Whether to search in parent themes
     * @param callable|null $finderModifier  Function to call on finder, used to apply
     *                                       any filter (like *.css) before copying files
     *
     * @return array                         An array with relative paths
     */
    public function scan(
        $relativePath = '',
        $filter = self::RESOURCE_FILTER_ALL,
        bool $ancestry = true,
        callable $finderModifier = null
    ) {
        $results = [];

        foreach ($this->getPaths() as $themepath) {
            if (!$startingPath = $this->makeStartingPath($relativePath)){
                continue;
            }

            $results = array_merge(
                $results,
                $this->searchInPath(
                    $startingPath,
                    $filter,
                    $themepath,
                    $finderModifier
                )
            );
        }

        if ($ancestry && $this->parentTheme) {
            $results = array_merge(
                $results,
                $this->parentTheme->scan($relativePath, $filter)
            );
        }

        return array_unique($results);
    }

    public function getFirstNonEmptyPath()
    {
        foreach ($this->paths as $path) {
            if (!empty($path)) {
                return $path;
            }
        }
    }

    protected function makeStartingPath(string $relativePath)
    {
        if (empty($relativePath) && $themepath = $this->getFirstNonEmptyPath()){
            if ($resource = $this->makeResource($themepath)) {
                return $resource->getRealPath();
            }
        }

        if ($resource = $this->getResource($relativePath)) {
            return $resource->getRealPath();
        }
    }

    protected function searchInPath(
        $fullpath,
        $filter,
        $rootpath,
        callable $finderModifier = null
    ): array {
        $results = [];
        $fs      = new Filesystem;
        $finder  = $this->getFinder($finderModifier);

        $finder->in($fullpath);

        switch ($filter) {
            case self::RESOURCE_FILTER_FILES:
                $finder->files();
                break;
            case self::RESOURCE_FILTER_DIRECTORIES:
                $finder->directories();
                break;
        }

        foreach ($finder as $object) {
            $result    = $object->getPathname();
            $result    = $fs->makePathRelative($result, $rootpath);
            $results[] = trim($result, '\/');
        }

        return $results;
    }

    /**
     * Initialize the Finder object to search for files and folders in a certain
     * directory
     *
     * @param callable|null $modifier  Optional function that allows to set
     *                                 additional criteria on the finder
     *
     * @return Finder
     */
    protected function getFinder(callable $modifier = null)
    {
        $finder = new Finder;

        $finder->ignoreUnreadableDirs()
               ->followLinks();

        if ($modifier) {
            $modifier($finder);
        }

        return $finder;
    }

    /**
     * Get all theme files as flat array, where keys are the relative paths, and
     * values are the paths to the source files, wherever those might reside in
     * theme hierarchy.
     *
     * @param bool $ancestry                 Whether to look in parent themes
     *
     * @param callable|null $finderModifier  Function to call on finder, used to apply
     *                                       any filter (like *.css) before copying files
     *
     * @return array
     */
    public function getFileList(
        bool $ancestry = true,
        callable $finderModifier = null
    ) {
        $tree = [];

        $resources = $this->getResourceList(
            '',
            self::RESOURCE_FILTER_FILES,
            $ancestry,
            $finderModifier
        );

        foreach ($resources as $resource) {
            $relativePath = $resource->getRelativePath();
            $realPath     = $resource->getRealPath();

            $tree[$relativePath] = $realPath;
        }

        return $tree;
    }

    /**
     * Collect all theme files from the multitude of possible theme locations
     * and from parent themes.
     *
     * @param string $destination            Path where to send the files. Default is the
     *                                       config('miravel.paths.storage')/theme-name
     * @param bool $ancestry                 Whether to look in parent themes
     * @param callable|null $finderModifier  Function to call on finder, useful for applying
     *                                       additional filters (like *.css) before copying files
     */
    public function dumpFileTree(
        string $destination = null,
        bool $ancestry = true,
        callable $finderModifier = null
    ) {
        if (!$destination) {
            $destination = $this->getDefaultThemeDumpPath();
        }

        $this->prepareDestinationDirectory($destination);

        $files = $this->getFileList($ancestry, $finderModifier);
        $fs    = new Filesystem;

        try {
            foreach ($files as $relativePath => $source) {
                $dest = implode(DIRECTORY_SEPARATOR, [
                    $destination,
                    $relativePath,
                ]);

                $fs->copy($source, $dest);
            }
        } catch (Exception $e) {
            MiravelFacade::exception(ThemeDumpException::class, ['file' => $source, 'dest' => $dest], __FILE__, __LINE__);
        }
    }

    protected function prepareDestinationDirectory(string $path, bool $safeCheckOff = false)
    {
        if (!$safeCheckOff && !$this->directoryPurgeSafeCheck($path)) {
            MiravelFacade::exception(PathPurgeSafeCheckException::class, compact('path'), __FILE__, __LINE__);
        }

        try {
            $this->purgeDirectory($path);
        } catch (Exception $exception) {
            MiravelFacade::exception(PathPurgeException::class, compact('path'), __FILE__, __LINE__);
        }
    }

    protected function directoryPurgeSafeCheck(string $path)
    {
        $safedir = MiravelFacade::getStoragePath();

        return Utilities::pathBelongsTo($path, $safedir);
    }

    protected function purgeDirectory(string $path)
    {
        Utilities::purgePath($path);
        Utilities::mkdir($path);
    }

    public function getDefaultThemeDumpPath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            MiravelFacade::getStoragePath(),
            $this->getName()
        ]);
    }

    /**
     * Build the theme
     */
    public function build()
    {
        $builder = $this->makeBuilder();

        $builder->build();
    }

    protected function makeBuilder(): ThemeBuilderInterface
    {
        return new LaravelMixThemeBuilder($this);
    }
}
