<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Enum\Entity\VariableTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Range;

class FixValuesListener implements EventSubscriberInterface
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
        switch ($parameter['variableType']) {
            case VariableTypes::VARIABLE_TYPE_BLOB:
                $parameter['valueUnsigned'] = null;
                $parameter['valueInteger'] = null;
                $parameter['valueBool'] = null;
                $parameter['valueString'] = null;
                break;
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                $parameter['valueBlob'] = null;
                $parameter['valueInteger'] = null;
                $parameter['valueBool'] = null;
                $parameter['valueString'] = null;
                break;
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
            case VariableTypes::VARIABLE_TYPE_SIGNED:
                $parameter['valueBlob'] = null;
                $parameter['valueUnsigned'] = null;
                $parameter['valueBool'] = null;
                $parameter['valueString'] = null;
                    break;
            case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                $parameter['valueInteger'] = null;
                $parameter['valueBlob'] = null;
                $parameter['valueUnsigned'] = null;
                $parameter['valueString'] = null;
                break;
            case VariableTypes::VARIABLE_TYPE_ASCII:
            case VariableTypes::VARIABLE_TYPE_STRING:
                $parameter['valueInteger'] = null;
                $parameter['valueDouble'] = null;
                $parameter['valueBlob'] = null;
                $parameter['valueUnsigned'] = null;
                $parameter['valueBool'] = null;
                $parameter['valueDate'] = null;
                break;
        }

        $event->setData($parameter);
    }
}