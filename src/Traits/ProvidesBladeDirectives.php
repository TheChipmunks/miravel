<?php

namespace Miravel\Traits;

use Miravel\Utilities;
use Miravel;
use Blade;

/**
 * Trait ProvidesBladeDirectives
 *
 * This trait allows Miravel to provide its own directives to the Blade engine.
 * Developers can customize directive aliases by using the 'blade_directive_map'
 * key of miravel config.
 *
 * The "themeextends" directive is missing because it cannot be implemented in
 * the standard way. It's implementation is in the BladeCompilerExtension class.
 *
 * @package Miravel
 */
trait ProvidesBladeDirectives
{
    /**
     * Implementation of Blade directive @themeinclude
     *
     * Look up for the included file within the current theme (theme containing
     * the view that requested the include).
     *
     * @param string $expression  the arguments to the directive.
     *
     * @return string             the resulting php code.
     */
    public function directiveThemeInclude(string $expression)
    {
        $expression = Blade::stripParentheses($expression);
        $expression = Utilities::stripQuotes($expression);
        $theme      = Miravel::getCurrentViewParentTheme();
        $path       = '';

        if ($theme) {
            $path = strval($theme->getViewFile($expression));
        }

        $directive = <<<'EOF'
<?php
    echo $__env->file(
        '%s', 
        array_except(get_defined_vars(), ['__data', '__path'])
    )->render();
?>
EOF;

        return sprintf($directive, $path);
    }

    /**
     * Implementation of Blade directive @element
     *
     * Shortcut to Miravel::element()
     *
     * @param string $expression  the expression representing the arguments to
     *                            the element (name, data and options).
     *
     * @return string             the resulting php code.
     */
    public function directiveElement(string $expression)
    {
        $expression = Blade::stripParentheses($expression);

        $directive = <<<'EOF'
<?php echo Miravel::element(%s) ?>
EOF;

        return sprintf($directive, $expression);
    }
}
