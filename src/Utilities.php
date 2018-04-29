<?php

namespace Miravel;

use Symfony\Component\Filesystem\Filesystem;
use Miravel\Facade as MiravelFacade;
use Illuminate\View\View;

class Utilities
{
    /**
     * @var Filesystem
     */
    protected static $fs;

    /**
     * @param string $path
     *
     * @return string|void
     */
    public static function extractClassNameFromFile(string $path)
    {
        if (!file_exists($path)) {
            return;
        }

        $code      = file_get_contents($path);
        $tokens    = token_get_all($code);
        $namespace = static::getNamespaceFromTokenArray($tokens);
        $classname = static::getClassnameFromTokenArray($tokens);

        if ($namespace && $classname) {
            return "\\$namespace\\$classname";
        }
    }

    public static function findTemplateInDirectory($path)
    {
        $templateFileName = (string)MiravelFacade::getConfig('template_file_name');
        $extensions       = (array) MiravelFacade::getConfig('template_file_extensions');

        foreach ($extensions as $extension) {
            $filename = "$templateFileName.$extension";
            $fullpath = implode(DIRECTORY_SEPARATOR, [$path, $filename]);
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }
    }

    public static function getVendorBasePath()
    {
        return dirname(__DIR__);
    }

    protected static function getNamespaceFromTokenArray(array $tokens): string
    {
        $declarationStarted = false;
        $namespace          = '';

        foreach ($tokens as $token) {

            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $declarationStarted = true;
            }

            if ($declarationStarted) {
                if (
                    is_array($token) &&
                    in_array($token[0], [T_STRING, T_NS_SEPARATOR])
                ) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    // Done with the namespace declaration
                    break;
                }
            }
        }

        return $namespace;
    }

    protected static function getClassnameFromTokenArray(array $tokens): string
    {
        $count = count($tokens);
        $classname = '';

        for ($i = 2; $i < $count; $i++) {
            if (
                is_array($tokens[$i - 2]) && $tokens[$i - 2][0] == T_CLASS &&
                is_array($tokens[$i - 1]) && $tokens[$i - 1][0] == T_WHITESPACE &&
                is_array($tokens[$i])     && $tokens[$i][0]     == T_STRING
            ) {
                $classname = $tokens[$i][1];
                break;
            }
        }

        return $classname;
    }

    /**
     * @param string $realpath
     *
     * @return bool
     */
    public static function isResourceViewPath($realpath)
    {
        $resourcePath = realpath(resource_path('views'));

        return 0 === strpos($realpath, $resourcePath);
    }

    /**
     * @param string $realpath
     *
     * @return bool
     */
    public static function isVendorPackagePath($realpath)
    {
        $vendorPath = static::getVendorBasePath();

        return 0 === strpos($realpath, $vendorPath);
    }

    public static function fillMoustachePlaceholders(
        string $message,
        array $values
    ) {
        $pattern = '/{([a-zA-Z0-9_]+)}/';

        return preg_replace_callback(
            $pattern,
            function ($matches) use ($values) {
            $varname = $matches[1];

            return $values[$varname] ?? '';
        }, $message);
    }

    public static function getTemplateBaseName($path)
    {
        $basename = basename($path);

        $extensions = (array)MiravelFacade::getConfig('template_file_extensions');

        // since extensions may contain each other (.blade.php and .php),
        // look for longer ones first
        usort($extensions, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($extensions as $extension) {
            $regex   = sprintf('/\.%s$/i', preg_quote($extension, '/'));
            $cleaned = preg_replace($regex, '', $basename);

            if ($cleaned != $basename) {
                return $cleaned;
            }
        }

        return $basename;
    }

    /**
     * Examine a view object and see if its path is inside a known theme.
     * If yes, return the theme object, otherwise null.
     *
     * @param View $view
     *
     * @return Theme|void
     */
    public static function viewBelongsToTheme(View $view)
    {
        $path = $view->getPath();

        return static::pathBelongsToTheme($path);
    }

    /**
     * Examine an arbitrary path and see if it is inside a known theme.
     * If yes, return the theme object; if no, return null.
     *
     * @param string $path  the path to examine
     *
     * @return Theme|void
     */
    public static function pathBelongsToTheme(string $path)
    {
        if ($themeName = static::getThemeNameFromPath($path)) {
            return MiravelFacade::makeAndValidateTheme($themeName);
        }
    }

    /**
     * If a path contains a theme name, return it.
     *
     * @param string $path  the path to the view file or directory.
     *
     * @return string|void
     */
    public static function getThemeNameFromPath(string $path)
    {
        $result = static::getPathComponents($path);

        if (!is_array($result)) {
            return;
        }

        return $result['theme'];
    }

    /**
     * Given an arbitrary filesystem path, try to extract root path, theme name,
     * component type, and component path relative to its theme.
     *
     * @param string $path
     *
     * @return array  ['root' => '...', 'theme' => '...', 'relative' => '...']
     */
    public static function getPathComponents(string $path)
    {
        $lookupPaths = Theme::getThemeDirPaths();
        $fs          = static::getFilesystem();

        foreach ($lookupPaths as $lookupPath) {
            if (!$lookupPath || !$fs->isAbsolutePath($lookupPath)) {
                continue;
            }

            $lookupPath = realpath($lookupPath);
            if (0 !== strpos($path, $lookupPath)) {
                continue;
            }

            $relative = substr($path, strlen($lookupPath));
            $relative = trim($relative, DIRECTORY_SEPARATOR);

            if (
                !strlen($relative) ||
                0 === strpos($relative, DIRECTORY_SEPARATOR)
            ) {
                continue;
            }

            $segments = explode(DIRECTORY_SEPARATOR, $relative);

            $components = [
                'root'     => $lookupPath,
                'theme'    => $segments[0],
            ];

            $components['type'] = (count($segments) > 2) ? $segments[1] : null;

            $components['relative'] = implode(
                DIRECTORY_SEPARATOR,
                array_slice($segments, 1)
            );

            return $components;
        }
    }

    /**
     * Tell is the file can be written even if it doesn't yet exist
     *
     * @param $file  an absolute path to file
     *
     * @return bool
     */
    public static function isWritable($file)
    {
        return (file_exists($file) && is_writable($file)) ||
               (!file_exists($file) && is_writable(dirname($file)));
    }

    public static function makeAbsolutePath($path)
    {
        if (static::isAbsolutePath($path)) {
            return $path;
        }

        // assume relative to base
        return base_path($path);
    }

    public static function isAbsolutePath($path)
    {
        $fs = static::getFilesystem();

        return $fs->isAbsolutePath($path);
    }

    public static function isWin()
    {
        return false !== stripos(PHP_OS, 'WIN');
    }

    public static function dotsToSlashes($name)
    {
        return str_replace('.', DIRECTORY_SEPARATOR, $name);
    }

    public static function stripQuotes($expression)
    {
        $expression = preg_replace('/^[\s\'"]*/', '', $expression);
        $expression = preg_replace('/[\s\'"]*$/', '', $expression);

        return $expression;
    }

    public static function parseDataAccessExpression($expression)
    {
        $steps   = explode('.', $expression);
        $varname = count($steps) ? array_shift($steps) : '';

        return compact('varname', 'steps');
    }

    public static function renderHtmlAttributes()
    {
        $attributes = (array)MiravelFacade::getConfig('html.html_tag_attributes');

        static::renderAttributeList($attributes);
    }

    public static function renderBodyAttributes()
    {
        $attributes = (array)MiravelFacade::getConfig('html.body_tag_attributes');

        static::renderAttributeList($attributes);
    }

    public static function renderAttributeList(array $attributes)
    {
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                if (is_numeric($name)) {
                    continue;
                }

                echo static::htmlAttr($name, $value, true);
            }
        }
    }

    public static function renderMetaTags()
    {
        $tags = (array)MiravelFacade::getConfig('html.meta_tags');

        $compiledTags = [];

        foreach ($tags as $tag) {
            if (!is_array($tag)) {
                continue;
            }

            $compiledAttributes = [];

            foreach ($tag as $attribute => $value) {
                if (is_numeric($attribute)) {
                    continue;
                }

                $compiledAttributes[] = static::htmlAttr($attribute, $value);
            }

            if (!empty($compiledAttributes)) {
                $compiledTags[] = sprintf(
                    '<meta %s>',
                    implode(' ', $compiledAttributes)
                );
            }
        }

        if (!empty($compiledTags)) {
            echo implode("\n", $compiledTags);
        }
    }

    public static function htmlAttr(
        string $name,
        string $value,
        bool $leadingSpace = false
    ) {
        $output = sprintf('%s="%s"', $name, htmlspecialchars($value));

        if ($leadingSpace) {
            $output = " $output";
        }

        return $output;
    }

    /**
     * Compare file names and tell if they are equal in terms of filesystem (ie
     * refer to the same file). On Windows, make comparison case insensitive,
     * correctly supporting multibyte names.
     *
     * @param $filename1
     * @param $filename2
     *
     * @return bool  true if file names are equal in terms of filesystem, false
     *               otherwise.
     */
    public static function FileNameCmp(string $filename1, string $filename2)
    {
        if (!static::isWin()) {
            return strcmp($filename1, $filename2) ? false : true;
        }

        return static::mbStrCmp($filename1, $filename2);
    }

    public static function mbStrCmp(string $str1, string $str2)
    {
        $encoding = mb_internal_encoding();

        $str1 = mb_strtoupper($str1, $encoding);
        $str2 = mb_strtoupper($str2, $encoding);

        return strcmp($str1, $str2) ? false : true;
    }

    public static function pathBelongsTo(string $path, string $parent)
    {
        // IMPORTANT: BOTH PATHS MUST EXIST!

        $path   = realpath($path);
        $parent = realpath($parent);

        return (strlen($path) >= strlen($parent)) &&
               (0 === strpos($path, $parent));
    }

    public static function getFilesystem()
    {
        if (!static::$fs) {
            static::$fs = new Filesystem;
        }

        return static::$fs;
    }

    public static function purgePath(string $path)
    {
        $fs = static::getFilesystem();
        $fs->remove($path);
    }

    public static function mkdir(string $path, int $mode = 0755)
    {
        $fs = static::getFilesystem();
        $fs->mkdir($path, $mode);
    }

    public static function fileExists(string $path)
    {
        $fs = static::getFilesystem();
        return $fs->exists($path);
    }

    public static function composePath(array $components)
    {
        // strip slashes first
        $clean = [];
        $components = array_values($components);

        foreach ($components as $i => $component) {
            // the first segment
            if (0 == $i) {
                $component = rtrim($component, '\/');
            }
            // the last segment
            if ((count($components) - 1) == $i) {
                $component = ltrim($component, '\/');
            }
            // middle segment
            if ((0 != $i) && (count($components) - 1) != $i) {
                $component = trim($component, '\/');
            }

            $clean[] = $component;
        }

        return implode(DIRECTORY_SEPARATOR, $clean);
    }

    /**
     * @param string $command
     *
     * @return CliCommandResult
     */
    public static function runCliCommand(string $command)
    {
        $return = 0;
        $output = [];

        exec($command, $output, $return);

        return new CliCommandResult($output, $return);
    }
}
