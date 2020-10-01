<?php

namespace App\Form\EventListener\Parameters\Odx;

use App\Entity\Units;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixUnitsListener implements EventSubscriberInterface
{
    /**
     * @var Units[]
     */
    private $units;

    public function __construct(array $units = [])
    {
        $this->units = $units;
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

        $form->add('unit', EntityType::class, [
            'class' => Units::class,
            'query_builder' => function (EntityRepository $repository)
            {
                return $repository->createQueryBuilder('u');
            },
            'data' => $this->units[$parameter->getUnit()]
        ]);
    }
}