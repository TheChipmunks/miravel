<?php

namespace Miravel;

use Illuminate\View\FileViewFinder as LaravelFileViewFinder;

use Miravel;

/**
 * Class FileViewFinder
 *
 * This is a replacement for the standard Laravel's view finder that can also
 * look for views by names relative to current theme; example: 'elements.name'
 *
 * Another super ability that we get from this class is that view names in the
 * 'miravel::' namespace can resolve not only to files directly, but to template
 * files within a folder, that is 'miravel::theme.elements.name' can be a folder
 * and the actual resolved template might be
 * 'resources/views/vendor/miravel/theme/elements/name/view.blade.php'
 *
 * cool, ha?
 *
 * @package Miravel
 */
class FileViewFinder extends LaravelFileViewFinder
{
    /**
     * Resolve views starting with "miravel::" namespace (layouts and elements).
     * If such a view is not found in the default location, will search up theme
     * hierarchy.
     *
     * @param string $name
     *
     * @return string|void
     */
    protected function findNamespacedView($name)
    {
        $parser = new ViewNameParser($name);

        if ($parser->isMiravelNamespacedView()) {
            $themeName = $parser->getTheme();
            $theme     = Miravel::makeAndValidateTheme($themeName);

            // miravel::theme.elements.name becomes just elements.name
            $relative  = $this->getNameRelativeToTheme($parser);

            if ($theme && ($path = $theme->getViewFile($relative))) {
                return $path;
            }
        }

        // fallback to normal Laravel resolving
        return parent::findNamespacedView($name);
    }

    /**
     * Get the name relative to the theme.
     * 'miravel::theme.elements.name' becomes just 'elements.name'
     *
     * @param ViewNameParser|string $name  the name or an already instantiated
     *                                     name parser
     *
     * @return string                      the relative name with namespace and
     *                                     theme name stripped out.
     */
    protected function getNameRelativeToTheme($name)
    {
        if (!$name instanceof ViewNameParser) {
            $name = new ViewNameParser($name);
        }

        $parts    = $name->getParts();
        $parts    = array_slice($parts, 1);
        $relative = implode('.', $parts);

        return $relative;
    }
}
