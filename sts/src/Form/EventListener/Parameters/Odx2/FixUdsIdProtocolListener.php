<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Enum\Entity\EcuCommunicationProtocols;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixUdsIdProtocolListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        if ($parameter['headerProtocol']
            == EcuCommunicationProtocols::ECU_COMMUNICATION_PROTOCOL_XCP_NAME) {
            $form->remove('udsId');
        }

        if ($parameter['headerProtocol']
            != EcuCommunicationProtocols::ECU_COMMUNICATION_PROTOCOL_UDS_XCP_NAME) {
            $form->remove('protocol');
        }
    }
}