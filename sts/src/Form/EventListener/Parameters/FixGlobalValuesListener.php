<?php

namespace App\Form\EventListener\Parameters;

use App\Enum\Entity\VariableTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixGlobalValuesListener implements EventSubscriberInterface
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
                    ->remove('valueUnsigned')
                    ->remove('valueBool')
                    ->remove('valueBigInteger')
                    ->remove('valueDouble')
                    ->remove('valueDate')
                    ->remove('valueSigned');
                break;
            case VariableTypes::VARIABLE_TYPE_INTEGER:
                $form
                    ->remove('valueString')
                    ->remove('valueUnsigned')
                    ->remove('valueBool')
                    ->remove('valueBigInteger')
                    ->remove('valueDouble')
                    ->remove('valueDate')
                    ->remove('valueSigned');
                break;
            case VariableTypes::VARIABLE_TYPE_DOUBLE:
                $form
                    ->remove('valueInteger')
                    ->remove('valueString')
                    ->remove('valueUnsigned')
                    ->remove('valueBool')
                    ->remove('valueDate')
                    ->remove('valueSigned')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_DATE:
                $form
                    ->remove('valueInteger')
                    ->remove('valueString')
                    ->remove('valueUnsigned')
                    ->remove('valueBool')
                    ->remove('valueDouble')
                    ->remove('valueSigned')
                    ->remove('valueBigInteger');
                break;
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                $form
                    ->remove('valueInteger')
                    ->remove('valueString')
                    ->remove('valueBool')
                    ->remove('valueBigInteger')
                    ->remove('valueDouble')
                    ->remove('valueDate')
                    ->remove('valueSigned');
                break;
            case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                $form
                    ->remove('valueInteger')
                    ->remove('valueUnsigned')
                    ->remove('valueString')
                    ->remove('valueBigInteger')
                    ->remove('valueDouble')
                    ->remove('valueDate')
                    ->remove('valueSigned');
                break;
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                $form
                    ->remove('valueInteger')
                    ->remove('valueUnsigned')
                    ->remove('valueString')
                    ->remove('valueBool')
                    ->remove('valueDouble')
                    ->remove('valueDate')
                    ->remove('valueSigned');
                break;
            case VariableTypes::VARIABLE_TYPE_SIGNED:
                $form
                    ->remove('valueInteger')
                    ->remove('valueUnsigned')
                    ->remove('valueBool')
                    ->remove('valueString')
                    ->remove('valueBigInteger')
                    ->remove('valueDate')
                    ->remove('valueDouble');
                break;
        }

        $form->add('globalParameterValueSetId', HiddenType::class)
            ->setData($parameter->getGlobalParameterValueSetId());
    }
}