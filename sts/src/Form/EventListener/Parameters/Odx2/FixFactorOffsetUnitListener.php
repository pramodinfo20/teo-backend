<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Enum\Entity\EcuSwParameterTypes;
use App\Form\Type\FloatType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FixFactorOffsetUnitListener implements EventSubscriberInterface
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
        if ($parameter->getType() == EcuSwParameterTypes::ECU_PARAMETER_TYPE_HW
            || $parameter->getType() == EcuSwParameterTypes::ECU_PARAMETER_TYPE_SW) {
            $form->remove('factor');
            $form->add('factor', HiddenType::class, [

            ]);
            $form->remove('offset');
            $form->add('offset', HiddenType::class, [

            ]);
            $form->remove('unit');
            $form->add('unit', HiddenType::class, [

            ]);
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }
        if ($parameter['type'] == EcuSwParameterTypes::ECU_PARAMETER_TYPE_HW
            || $parameter['type'] == EcuSwParameterTypes::ECU_PARAMETER_TYPE_SW
            || $parameter['type'] == EcuSwParameterTypes::ECU_PARAMETER_TYPE_SERIAL) {
        } elseif (array_key_exists('offset', $parameter)) {
            if ($parameter['offset'] == '' || (float)$parameter['offset'] == 0) {
                $parameter['offset'] = 0;

                $event->setData($parameter);
            }
        }
    }

}