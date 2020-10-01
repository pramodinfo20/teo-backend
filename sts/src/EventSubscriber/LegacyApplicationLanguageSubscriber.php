<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LegacyApplicationLanguageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        /*
         * Legacy application must react on language switching so we used this listener
         */
        if ($request->headers->has('legacy-application-language')
            && $language = $request->headers->get('legacy-application-language')) {
            $request->setLocale($language);
        }
    }
}