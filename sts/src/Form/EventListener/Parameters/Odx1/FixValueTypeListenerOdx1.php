<?php


namespace App\Form\EventListener\Parameters\Odx1;



use App\Enum\Entity\EcuSwParameterTypes;
use App\Enum\Entity\VariableTypes;
use App\Service\Ecu\Sw\Parameter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Enum\Parameter as ParameterEnum;


class FixValueTypeListenerOdx1 implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        $rights = $parameter->isRead() && !$parameter->isWrite() && !$parameter->isConfirm();

        if (in_array($parameter->getType(), [EcuSwParameterTypes::ECU_PARAMETER_TYPE_HW, EcuSwParameterTypes::ECU_PARAMETER_TYPE_PARAMETER] )) {
            switch (Parameter::getLinkingTypeByName($parameter->getLinkingType())) {
                case ParameterEnum::LINKING_TYPE_DEFAULT:
                case ParameterEnum::LINKING_TYPE_CONSTANT:
                    $this->toggleValue(VariableTypes::getVariableTypeByName($parameter->getVariableType()), $form, $rights);
                    break;
                case ParameterEnum::LINKING_TYPE_DYNAMIC_VALUE:
                    $this->changeVisibility($form, 'dynamicParameterValuesByDiagnosticSoftware', $rights);
                    break;
                case ParameterEnum::LINKING_TYPE_GLOBAL_PARAMETER:
                    $this->changeVisibility($form, 'linkedToGlobalParameter', $rights);
                    break;
                /*case ParameterEnum::LINKING_TYPE_COC_PARAMETER:
                    $this->changeVisibility($form, 'linkedToCocParameter', $rights);
                    break;*/
            }
        } else if (EcuSwParameterTypes::ECU_PARAMETER_TYPE_SW) {
            $this->toggleValue(VariableTypes::getVariableTypeByName($parameter->getVariableType()), $form, $rights);
        }
    }

    function toggleValue($variableType, &$form, $rights)
    {
        switch ($variableType) {
            case VariableTypes::VARIABLE_TYPE_ASCII:
            case VariableTypes::VARIABLE_TYPE_STRING:
                $this->changeVisibility($form, 'valueString', $rights);
                break;
            case VariableTypes::VARIABLE_TYPE_BLOB:
                $this->changeVisibility($form, 'valueBlob', $rights);
                break;
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
            case VariableTypes::VARIABLE_TYPE_SIGNED:
                $this->changeVisibility($form, 'valueInteger', $rights);
                break;
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                $this->changeVisibility($form, 'valueUnsigned', $rights);
                break;
            case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                $this->changeVisibility($form, 'valueBool', $rights);
                break;
        }
    }

    function changeVisibility(&$form, $variable, $rights)
    {

        $variableTypes = [
            'valueString',  'valueInteger', 'valueUnsigned',  'valueBool', 'linkedToGlobalParameter',
            'dynamicParameterValuesByDiagnosticSoftware' ]; /*'linkedToCocParameter'*/

        foreach ($variableTypes as $type) {
            $config = $form->get($type);
            $config = $config->getConfig();
            $options = $config->getOptions();

            if ($type == $variable) {
                $options = array_replace($options, [
                    'attr' => [
                        'class' => 'customShow'
                    ],
                    'disabled' => $rights
                ]);
            } else {
                $options = array_replace($options, [
                    'attr' => [
                        'class' => 'customHide'
                    ],
                    'disabled' => $rights
                ]);
            }

            $form->add($type, get_class($config->getType()->getInnerType()), $options);
        }
    }
}