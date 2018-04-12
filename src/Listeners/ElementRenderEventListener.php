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
        $signedName = $event->element->getSignedName();

        MiravelFacade::registerTopLevelRenderingElement($signedName);
    }

    protected function handleFinishElementRender(FinishElementRenderEvent $event)
    {
        $signedName = $event->element->getSignedName();

        MiravelFacade::unregisterTopLevelRenderingElement($signedName);
    }
}
