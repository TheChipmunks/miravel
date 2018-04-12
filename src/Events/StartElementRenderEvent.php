<?php

namespace Miravel\Events;

use Miravel\Element;

class StartElementRenderEvent
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
