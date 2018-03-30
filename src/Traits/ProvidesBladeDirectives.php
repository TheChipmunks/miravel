<?php

namespace Miravel\Traits;

use Exception;
use Miravel\Exceptions\ViewResolvingException;
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
     *
     * @throws ViewResolvingException
     */
    public function directiveThemeinclude(string $expression)
    {
        return $this->getViewRenderCode($expression, 'themeinclude');
    }

    /**
     * This one is used by BladeCompilerExtendion rather that added as a regular
     * directive, hence no word "directive" in the name
     *
     * @param string $expression  the name of the view to extend
     *
     * @returns string            the code that renders the extended view
     *
     * @throws ViewResolvingException
     */
    public function themeextends(string $expression)
    {
        return $this->getViewRenderCode($expression, 'themeextends');
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

    /**
     * Render some property of the element data object.
     *
     * @param $expression  the expression supplied by the user
     *
     * @return string      the code to render the property
     */
    public function directiveProp($expression)
    {
        $directive = '<?php echo $element->get(%s); ?>';
        $directive = sprintf($directive, $expression);

        return $directive;
    }

    public function directiveEprop($expression)
    {
        $directive = '<?php echo e($element->get(%s)); ?>';
        $directive = sprintf($directive, $expression);

        return $directive;
    }







    /**
     * Resolve the view name relative to the theme and return the code that will
     * render this view.
     *
     * @param string $expression the view name relative to the current theme
     *
     * @param string $forDirective the directive name, so we can make our
     *                              Exceptions more informative
     *
     * @return string               the code to insert in the compiled view
     *
     * @throws ViewResolvingException
     */
    protected function getViewRenderCode(string $expression, string $forDirective): string
    {
        $expression = Blade::stripParentheses($expression);
        $expression = Utilities::stripQuotes($expression);

        try {
            $path = $this->resolveThemeView($expression);
        } catch (Exception $exception) {
            Throw new ViewResolvingException([
                'directive'   => $forDirective,
                'callingview' => Miravel::getCurrentView(),
                'error'       => $expression->getMessage(),
            ]);
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
     * Resolve the view name relative to current theme.
     *
     * @param $expression  the view name to resolve
     *
     * @return string      the resolved view path
     *
     * @throws Exception
     */
    protected function resolveThemeView($expression)
    {
        if (!$view = Miravel::getCurrentView()) {
            throw new Exception('unable to figure out current view');
        }

        if (!$theme = Miravel::getCurrentViewParentTheme()) {
            throw new Exception('unable to figure out current theme');
        }

        if (!$path = $theme->getViewFile($expression)) {
            throw new Exception(sprintf(
                'unable to resolve view "%s" from theme "%s"',
                $expression,
                $theme->getName()
            ));
        }

        return $path;
    }
}
