<?php

namespace App\Form\EventListener\Parameters\Header;

use App\Enum\Entity\ConfigurationEcus;
use App\Enum\Entity\EcuCommunicationProtocols;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FixRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit'
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $header = $event->getData();
        $form = $event->getForm();

        if ($header->getProtocol()->getEcuCommunicationProtocolName()
            != EcuCommunicationProtocols::getProtocolNameById(EcuCommunicationProtocols::ECU_COMMUNICATION_PROTOCOL_XCP)) {
            if (in_array($header->getEcuId(), [ConfigurationEcus::CDIS, ConfigurationEcus::BCM])) {
                $form->add('request', TextType::class, [
                    'constraints' => [
                        new notBlank()
                    ]
                ]);
            } else {
                $form->add('request', TextType::class, [

                ]);
            }

        } else {
            $form->add('request', TextType::class, [
                'disabled' => true
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $header = $event->getData();
        $form = $event->getForm();

        if ($header['protocol'] != EcuCommunicationProtocols::ECU_COMMUNICATION_PROTOCOL_XCP) {
            $request = $header['request'];

            $form->remove('request');
            if (in_array($header['ecuId'], [ConfigurationEcus::CDIS, ConfigurationEcus::BCM])) {
                $form->add('request', TextType::class, [
                    'constraints' => [
                        new notBlank()
                    ]
                ])->setData($request);
            } else {
                $form->add('request', TextType::class, [

                ])->setData($request);
            }
        } else {
            $form->remove('request');
            $form->add('request', TextType::class, [
                'disabled' => true
            ]);
        }
    }
}