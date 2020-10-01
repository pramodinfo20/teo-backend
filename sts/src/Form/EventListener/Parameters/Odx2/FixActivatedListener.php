<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Enum\Entity\EcuSwParameterTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class FixActivatedListener implements EventSubscriberInterface
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

        if (in_array($parameter->getType(), EcuSwParameterTypes::getEditableTypes())) {
            $form->remove('activated');
            $form->add('activated', CheckboxType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'visibility: hidden'
                ],
                'data' => true
            ]);
        }

        if (is_null($form->get('activated'))) {
            $parameter->setActivated(false);
        }
    }
}