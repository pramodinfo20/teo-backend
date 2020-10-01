<?php

namespace App\Form\EventListener\Parameters\Odx;

use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use App\Enum\Parameter as ParameterEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class FixLinkingTypeListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit'
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW) {
            $form->add('linkingType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('choices.eventListener.odx.types.default', [], 'forms') => ParameterEnum::LINKING_TYPE_DEFAULT,
                    $this->translator->trans('choices.eventListener.odx.types.constant', [], 'forms') => ParameterEnum::LINKING_TYPE_CONSTANT,
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => $parameter->getLinkingType(),
            ]);
        } elseif ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW) {
            $form->add('linkingType', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => ParameterEnum::LINKING_TYPE_CONSTANT,
            ]);
        } else {
            $form->add('linkingType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('choices.eventListener.odx.types.default', [], 'forms')  => ParameterEnum::LINKING_TYPE_DEFAULT,
                    $this->translator->trans('choices.eventListener.odx.types.constant', [], 'forms') => ParameterEnum::LINKING_TYPE_CONSTANT,
                    $this->translator->trans('choices.eventListener.odx.types.global', [], 'forms') => ParameterEnum::LINKING_TYPE_GLOBAL_PARAMETER,
                    $this->translator->trans('choices.eventListener.odx.types.dynamic', [], 'forms') => ParameterEnum::LINKING_TYPE_DYNAMIC_VALUE,
                    /*$this->translator->trans( 'choices.eventListener.odx.types.coc', [], 'forms') => ParameterEnum::LINKING_TYPE_COC_PARAMETER*/
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'data' => $parameter->getLinkingType(),
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        /* Remove validation for specific rights case */
        if (isset($parameter['read']) && $parameter['read']
            && (!isset($parameter['write']) && !isset($parameter['confirm']))) {
            $form->remove('linkingType');
        }
    }
}