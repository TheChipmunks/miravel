<?php

namespace Miravel\Events;

class FinishElementRenderEvent
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
