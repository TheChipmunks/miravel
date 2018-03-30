<?php

namespace Miravel;

use Miravel\Facade as MiravelFacade;
use Illuminate\View\View;

class Utilities
{
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

    public static function viewBelongsToTheme(View $view)
    {
        $path = $view->getPath();

        return static::pathBelongsToTheme($path);
    }

    public static function pathBelongsToTheme(string $path)
    {
        if ($themeName = Theme::getThemeNameFromViewPath($path)) {
            return MiravelFacade::makeAndValidateTheme($themeName);
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
        return static::isWin() ?
            preg_match('/^[a-z]:/i', $path) :
            0 === strpos($path, '/');
    }

    public static function isWin()
    {
        return false !== stripos(PHP_OS, 'WIN');
    }

    public static function viewNameDotsToSlashes($name)
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
}
