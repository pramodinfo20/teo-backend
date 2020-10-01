<?php

namespace App\Form\EventListener\Parameters;

use App\Enum\Entity\VariableTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixCocValuesListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        switch ($parameter->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_STRING:
            case VariableTypes::VARIABLE_TYPE_ASCII:
            case VariableTypes::VARIABLE_TYPE_BLOB:
                $form
                    ->remove('valueInteger')
                    ->remove('valueDouble')
                    ->remove('valueBool')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_INTEGER:
                $form
                    ->remove('valueString')
                    ->remove('valueDouble')
                    ->remove('valueBool')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                $form
                    ->remove('valueDouble')
                    ->remove('valueString')
                    ->remove('valueBool')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_DOUBLE:
                $form
                    ->remove('valueInteger')
                    ->remove('valueString')
                    ->remove('valueBool')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                $form
                    ->remove('valueInteger')
                    ->remove('valueDouble')
                    ->remove('valueString')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                $form
                    ->remove('valueInteger')
                    ->remove('valueDouble')
                    ->remove('valueString')
                    ->remove('valueBool')
                    ->remove('valueDate');
                break;
            case VariableTypes::VARIABLE_TYPE_DATE:
                $form
                    ->remove('valueInteger')
                    ->remove('valueDouble')
                    ->remove('valueBool')
                    ->remove('valueString')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_SIGNED:
                $form
                    ->remove('valueDouble')
                    ->remove('valueBool')
                    ->remove('valueString')
                    ->remove('valueDate')
                    ->remove('valueBigInteger');
                break;
        }
    }
}