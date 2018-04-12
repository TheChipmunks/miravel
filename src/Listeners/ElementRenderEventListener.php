<?php

namespace Miravel\Listeners;

use Miravel\Events\FinishElementRenderEvent;
use Miravel\Events\StartElementRenderEvent;
use Miravel\Facade as MiravelFacade;

class ElementRenderEventListener
{
    public function handle($event)
    {
        if ($event instanceof StartElementRenderEvent) {
            $this->handleStartElementRender($event);
        }

        if ($event instanceof FinishElementRenderEvent) {
            $this->handleFinishElementRender($event);
        }
    }

    protected function handleStartElementRender(StartElementRenderEvent $event)
    {
        $fqn = $event->element->getFullyQualifiedName();

        MiravelFacade::registerTopLevelRenderingElement($fqn);
    }

    protected function handleFinishElementRender(FinishElementRenderEvent $event)
    {
        $fqn = $event->element->getFullyQualifiedName();

        MiravelFacade::unregisterTopLevelRenderingElement($fqn);
    }
}
