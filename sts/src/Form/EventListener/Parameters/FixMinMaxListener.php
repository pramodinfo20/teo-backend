<?php

namespace App\Form\EventListener\Parameters;

use App\Entity\EcuSoftwareParameterNames;
use App\Enum\Entity\EcuSwParameterTypes;
use App\Enum\Entity\VariableTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FixMinMaxListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter || is_null($parameter['variableType'])) {
            return;
        }

        if (in_array($parameter['variableType'], [
            VariableTypes::VARIABLE_TYPE_BOOLEAN,
            VariableTypes::VARIABLE_TYPE_STRING,
            VariableTypes::VARIABLE_TYPE_BLOB,
            VariableTypes::VARIABLE_TYPE_ASCII])) {
            $form->remove('minValue');
            $form->remove('maxValue');
            $form->add('minValue', TextType::class, []);
            $form->add('maxValue', TextType::class, []);
        }

    }
}