<?php

namespace App\EventSubscriber;

use App\Controller\LegacyBaseController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LegacyApplicationUrlSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        /*
         * Legacy application sometimes need URL's to back forms
         */
        if ($controller[0] instanceof LegacyBaseController) {
            if ($event->getRequest()->headers->has('legacy-application-url')
                && $url = $event->getRequest()->headers->get('legacy-application-url')) {
                $controller[0]->setUrlToLegacyApplication($url);
            }
        }
    }
}