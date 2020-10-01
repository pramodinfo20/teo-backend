<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 9:15 AM
 */

namespace App\Form\EventListener\SubConfiguration;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddProductionDepotsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $placesOfProduction;

    public function __construct(array $placesOfProduction = [])
    {
        $this->placesOfProduction = $placesOfProduction;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $subConfiguration = $event->getData();
        $form = $event->getForm();

        $places = [];
        foreach ($this->placesOfProduction as $place) {
            $places[$place->getName()] = $place->getDepotId();
        }

        $places = array_merge([' --- ' => null], $places);

        $form->add('stsPlaceOfProduction', ChoiceType::class, [
            'choices' => $places,
            'constraints' => [
                new NotBlank()
            ]
        ]);
    }
}