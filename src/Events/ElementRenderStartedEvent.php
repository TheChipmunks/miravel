<?php

namespace Miravel\Events;

use Miravel\Element;

class ElementRenderStartedEvent
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
