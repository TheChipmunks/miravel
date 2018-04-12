<?php

namespace Miravel;

use Miravel\Traits\ProvidesBladeDirectives;
use Miravel\Facade as MiravelFacade;

/**
 * Class BladeCompilerExtension
 *
 * An extension to Blade Compiler Engine adding support for the "themeextends"
 * directive (which works by adding custom footers to views).
 *
 * @package Miravel
 */
class BladeCompilerExtension
{
    use ProvidesBladeDirectives;

    /**
     * @var array
     */
    protected $footers = [];

    /**
     * @var string
     */
    protected $viewContents;

    /**
     * BladeCompilerExtension constructor.
     *
     * @param string $viewContents  the contents of the view file.
     */
    public function __construct(string $viewContents)
    {
        $this->viewContents = $viewContents;
    }

    /**
     * Process the view file.
     *
     * @return string  the compiled view.
     */
    public function process(): string
    {
        $this->compileThemeExtends();

        return $this->viewContents;
    }

    /**
     * Compile the "themeextends" directive. If the developer has provided an
     * alias to this directive, take that into account.
     *
     * @return void
     */
    protected function compileThemeExtends()
    {
        $directive = $this->getExtendDirectiveAlias();
        $directive = preg_quote($directive, '/');
        $regex     = sprintf('/@%s\s*\((.+?)\)/', $directive);

        $this->viewContents = preg_replace_callback(
            $regex,
            [$this, 'processThemeExtendsDirective'],
            $this->viewContents
        );

        $this->appendFooters();
    }

    /**
     * Process the "themeextends" directives in the file, adding a custom footer
     * for each one (there shouldn't be more than one, really).
     *
     * @param array $matches the array of detected directives
     *
     * @return string         the directives will be removed, i.e. replaced by
     *                        an empty string. That's how Laravel's native
     *                        "extends" also works.
     *
     * @throws Exceptions\ViewResolvingException
     */
    protected function processThemeExtendsDirective(array $matches): string
    {
        $this->footers[] = $this->getFooterExpression($matches[1]);

        return '';
    }

    /**
     * All layouts were saved as footers and will be compiled at the end of the
     * current view. Append the code rendering the layouts.
     *
     * @return void
     */
    protected function appendFooters()
    {
        foreach ($this->footers as $footer) {
            $this->viewContents .= "\n$footer";
        }
    }

    /**
     * Get the php code that will render a layout.
     *
     * @param $expression  the expression representing directive arguments (the
     *                     layout name, normally).
     *
     * @return string      the php code rendering the layout.
     *
     * @throws Exceptions\ViewResolvingException
     */
    protected function getFooterExpression($expression): string
    {
        return $this->themeextends($expression);
    }

    /**
     * If the developer wants to use another name for "themeextends", find out
     * which one.
     *
     * @return string
     */
    protected function getExtendDirectiveAlias(): string
    {
        $default = 'themeextends';
        $name    = MiravelFacade::getConfig(
            'blade_directive_map.themeextends',
            $default
        );

        return is_string($name) ? $name : $default;
    }
}
