<?php

namespace Miravel\Themes\TestParent;

use Miravel\Element;

class Element02 extends Element
{
    public function render(): string
    {
        $class = get_class($this);

        $output = parent::render();

        $output .= "<div>Class: $class, theme: <strong>parent</strong>, location: <strong>vendor</strong></div>";

        return $output;
    }
}
