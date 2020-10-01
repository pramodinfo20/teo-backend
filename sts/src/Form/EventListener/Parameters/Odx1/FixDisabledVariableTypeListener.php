<?php

namespace App\Form\EventListener\Parameters\Odx1;

use App\Entity\VariableTypes;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixDisabledVariableTypeListener implements EventSubscriberInterface
{
    /**
     * @var VariableTypes[]
     */
    private $variableTypes;

    public function __construct(array $variableTypes = [])
    {
        $this->variableTypes = $variableTypes;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $parameter = $event->getData();
        $form = $event->getForm();

        if (!$parameter) {
            return;
        }

        $form->add('variableType', EntityType::class, [
            'class' => VariableTypes::class,
            'query_builder' => function (EntityRepository $repository)
            {
                return $repository->createQueryBuilder('v')
                    ->where('v.variableTypeId NOT IN (:ids)')
                    ->setParameter('ids', [
                        VariableTypesEnum::VARIABLE_TYPE_DOUBLE,
                        VariableTypesEnum::VARIABLE_TYPE_DATE
                    ]);
            },
            'data' => $this->variableTypes[$parameter->getVariableType()],
            'disabled' => true
        ]);
    }
}