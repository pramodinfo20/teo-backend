<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Enum\Entity\EcuSwParameterTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixSerialListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        /* Set specific id's to catch in jQuery */
        if ($parameter->getType() == EcuSwParameterTypes::ECU_PARAMETER_TYPE_SERIAL) {
            $form
                ->add('name', TextType::class, [
                    'attr' => [
                        'style' => 'width: 133px',
                        'readonly' => 'readonly'
                    ]
                ])
                ->add('serialState', CheckboxType::class)
                ->add('read', CheckboxType::class, [
                    'attr' => [
                        'class' => 'serialReadRights',
                        'readonly' => true
                    ]
                ])
                ->add('write', CheckboxType::class, [
                    'attr' => [
                        'class' => 'serialWrite'
                    ]
                ])
                ->add('confirm', CheckboxType::class, [
                    'attr' => [
                        'class' => 'serialConfirm'
                    ]
                ])
                ->remove('factor')
                ->remove('offset')
                ->remove('unit')
                ->remove('coding')
                ->add('coding', HiddenType::class)
                ->add('valueString', HiddenType::class)
                ->add('valueBlob', HiddenType::class)
                ->add('valueInteger', HiddenType::class)
                ->add('valueUnsigned', HiddenType::class)
                ->add('valueBool', HiddenType::class)
                ->remove('linkingType');
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        /* Remove from validation */
        if (isset($parameter['type']) && $parameter['type'] == EcuSwParameterTypes::ECU_PARAMETER_TYPE_SERIAL) {
            $form
                ->remove('write')
                ->remove('confirm');
        }
    }
}