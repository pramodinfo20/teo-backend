<?php

namespace App\Form\EventListener\Configuration\Odx;

use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
        if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL) {
            $form
                ->add('parameterId', HiddenType::class)
                ->add('overwrittenValueSetId', HiddenType::class)
                ->add('order', HiddenType::class)
                ->add('name', HiddenType::class)
                ->add('serialState', HiddenType::class)
                ->add('read', HiddenType::class)
                ->add('write', HiddenType::class)
                ->add('confirm', HiddenType::class)
                ->remove('factor')
                ->remove('offset')
                ->remove('unit')
                ->add('valueString', HiddenType::class)
                ->add('valueBlob', HiddenType::class)
                ->add('valueInteger', HiddenType::class)
                ->add('valueUnsigned', HiddenType::class)
                ->add('valueBool', HiddenType::class)
                ->remove('linkingTypeName');
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
        if (isset($parameter['type']) && $parameter['type'] == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL) {
            $form
                ->remove('write')
                ->remove('confirm');
        }
    }
}