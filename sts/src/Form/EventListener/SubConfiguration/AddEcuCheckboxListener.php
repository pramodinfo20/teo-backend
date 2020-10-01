<?php

namespace App\Form\EventListener\SubConfiguration;

use App\Form\Transformer\Vehicle_conf\StringToArrayTransformer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddEcuCheckboxListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $allEcus;

    private $swMapping;

    private $builder;

    public function __construct($builder = null, array $allEcus = [],  $swMapping = null)
    {
        $this->allEcus = $allEcus;
        $this->swMapping = $swMapping;
        $this->builder = $builder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $transformer = new StringToArrayTransformer();
        $subConfiguration = $event->getData();
        $form = $event->getForm();

        $ecus = [];
        $cans = [];
        $i = 0;

        foreach ($this->allEcus as $ecu) {
            $ecus[$ecu->getEcuName()] = $ecu->getCeEcuId();
            $cans[$i] = $ecu->getCeEcuId();
            $i++;
        }

        $mappedArray = [];

        if (!is_null($subConfiguration->getSubConfigurationId())) {
            $mappedSws = $this->swMapping->findBy(['subVehicleConfiguration' => $subConfiguration->getSubConfigurationId()]);

            foreach ($mappedSws as $sw) {
                if ($sw->getIsPrimarySw()) {
                    $mappedArray[$sw->getEcuSwVersion()->getCeEcu()->getCeEcuId()]['primary']['sw'] = $sw->getEcuSwVersion()->getSwVersion();
                } else {
                    $mappedArray[$sw->getEcuSwVersion()->getCeEcu()->getCeEcuId()]['alternative']['sw'] = $sw->getEcuSwVersion()->getSwVersion();
                }
            }
        }

        $form->add('ecus', ChoiceType::class, [
            'choices' => $ecus,
            'multiple' => true,
            'expanded' => true,
            'choice_attr' => function ($val, $key, $index) use ($mappedArray)
            {
                return [
                    'data-primary-sw' => isset($mappedArray[$val]['primary']) ? $mappedArray[$val]['primary']['sw'] : null,
                    'data-alternative-sw' => isset($mappedArray[$val]['alternative']) ? $mappedArray[$val]['alternative']['sw'] : null,
                ];
            },
        ]);

        $form->add($this->builder->create('cans', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
            'choices' => $cans,
            'multiple' => false,
            'expanded' => true,
            'auto_initialize' => false
        ))->addModelTransformer($transformer)->getForm());
    }
}