<?php

namespace App\Form\EventListener\Parameters\Odx;

use App\Enum\Entity\EcuSoftwareParameterNames;
use App\Enum\Entity\EcuSwParameterTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixCopyStsPartNumberListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!is_null($parameter)) {

            $type = $parameter->getType();
            $nameId = $parameter->getNameId();

            if ($type != EcuSwParameterTypes::ECU_PARAMETER_TYPE_PARAMETER) {
                if ($nameId == EcuSoftwareParameterNames::ECU_PARAMETER_NAME_HW_SUPPLIER
                    || $nameId == EcuSoftwareParameterNames::ECU_PARAMETER_NAME_SW_SUPPLIER) {
                    $form->add('copySts', ButtonType::class, [
                        'attr' => ['class' => 'copySts'],
                        'disabled' => true
                    ]);
                } else {
                    $form->add('copySts', ButtonType::class, [
                        'attr' => ['class' => 'copySts']
                    ]);
                }
            } else {
                $form->add('copySts', HiddenType::class, [
                    'disabled' => true,
                    'mapped' => false
                ]);
            }
        } else {
            $form->add('copySts', HiddenType::class, [
                'disabled' => true,
                'mapped' => false
            ]);
        }
    }
}