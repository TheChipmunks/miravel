<?php

namespace Miravel\Events;

use Miravel\Element;

class ElementRenderFinishedEvent
{
    /**
     * @var Element
     */
    public $element;

    public function __construct(Element $element)
    {
        $this->element = $element;
    }
}
