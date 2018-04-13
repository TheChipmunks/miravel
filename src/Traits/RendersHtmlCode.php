<?php

namespace Miravel\Traits;

use Miravel\Utilities;

trait RendersHtmlCode
{
    /**
     * Helper function to render a list of attributes for the "html" tag, based
     * on current project config (config/miravel.php)
     */
    public function renderHtmlAttributes()
    {
        return Utilities::renderHtmlAttributes();
    }

    /**
     * Helper function to render a list of attributes for the "body" tag, based
     * on current project config (config/miravel.php)
     */
    public function renderBodyAttributes()
    {
        return Utilities::renderBodyAttributes();
    }

    /**
     * Helper function to render a list of meta tags, specified in current
     * project config (config/miravel.php)
     */
    public function renderMetaTags()
    {
        return Utilities::renderMetaTags();
    }
}
