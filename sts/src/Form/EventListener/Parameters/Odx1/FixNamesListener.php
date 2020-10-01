<?php

namespace App\Form\EventListener\Parameters\Odx1;

use App\Entity\EcuSoftwareParameterNames;
use App\Enum\Entity\EcuSwParameterTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FixNamesListener implements EventSubscriberInterface
{
    /**
     * @var EcuSoftwareParameterNames[]
     */
    private $parameterNames;

    public function __construct(array $parameterNames = [])
    {
        $this->parameterNames = $parameterNames;
    }

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

        /* First remove old field and replace with new one */
        if (!in_array($parameter->getType(), EcuSwParameterTypes::getEditableTypes())) {
            $form->remove('name');
            $form->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'readOnly' => true,
                    'size' => 40
                ]
            ]);
        }

    }
}