<?php

namespace Miravel\Themes\TestСhild;

use Miravel\Element;

class Element02 extends Element
{
    public function render(): string
    {
        $class = get_class($this);

        $output = parent::render();

        $output .= "<div>Class: $class, theme: <strong>child</strong>, location: <strong>vendor</strong></div>";

        return $output;
    }
}
