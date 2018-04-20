<?php

namespace Miravel\Listeners;

use Miravel\Events\ElementRenderFinishedEvent;
use Miravel\Events\ElementRenderStartedEvent;
use Miravel\Facade as MiravelFacade;

class ElementRenderEventListener
{
    public function handle($event)
    {
        if ($event instanceof ElementRenderStartedEvent) {
            $this->handleStartElementRender($event);
        }

        if ($event instanceof FinishElementRenderEvent) {
            $this->handleFinishElementRender($event);
        }
    }

    protected function handleStartElementRender(ElementRenderStartedEvent $event)
    {
        $signedName = $event->element->getSignedName();

        MiravelFacade::registerTopLevelRenderingElement($signedName);
    }

    protected function handleFinishElementRender(ElementRenderFinishedEvent $event)
    {
        $signedName = $event->element->getSignedName();

        MiravelFacade::unregisterTopLevelRenderingElement($signedName);
    }
}
