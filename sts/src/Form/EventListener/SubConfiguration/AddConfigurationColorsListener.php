<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 9:35 AM
 */

namespace App\Form\EventListener\SubConfiguration;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddConfigurationColorsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $allColors;

    public function __construct(array $allColors = [])
    {
        $this->allColors = $allColors;
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

        $colors = [];
        foreach ($this->allColors as $color) {
            $colors[$color->getConfigurationColorName()] = $color->getConfigurationColorId();
        }

        $colors = array_merge([' --- ' => null], $colors);

        $form->add('standardColor', ChoiceType::class, [
            'choices' => $colors,
            'constraints' => [
                new NotBlank()
            ]
        ]);
    }
}