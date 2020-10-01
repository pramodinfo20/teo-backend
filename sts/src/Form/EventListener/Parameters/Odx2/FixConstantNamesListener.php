<?php

namespace App\Form\EventListener\Parameters\Odx2;

use App\Entity\EcuSoftwareParameterNames;
use App\Enum\Entity\EcuSoftwareParameterNames as EcuSoftwareParameterNamesEnum;
use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixConstantNamesListener implements EventSubscriberInterface
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
        if (in_array($parameter->getType(), EcuSwParameterTypesEnum::getEditableTypes())) {
            $form->remove('name');
        }

        switch ($parameter->getType()) {
            case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW:
                $form->add('name', EntityType::class, [
                    'class' => EcuSoftwareParameterNames::class,
                    'query_builder' => function (EntityRepository $repository)
                    {
                        return $repository->createQueryBuilder('n')
                            ->where('n.ecuSoftwareParameterNameId IN(:ids)')
                            ->setParameter('ids', [
                                EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_HW_STS,
                                EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_HW_SUPPLIER
                            ]);
                    },
                    'data' => $this->parameterNames[$parameter->getName()]
                ]);
                break;
            case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW:
                $form->add('name', EntityType::class, [
                    'class' => EcuSoftwareParameterNames::class,
                    'query_builder' => function (EntityRepository $repository)
                    {
                        return $repository->createQueryBuilder('n')
                            ->where('n.ecuSoftwareParameterNameId IN(:ids)')
                            ->setParameter('ids', [
                                EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_SW_STS,
                                EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_SW_SUPPLIER
                            ]);
                    },
                    'data' => $this->parameterNames[$parameter->getName()]
                ]);
                break;
            case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL:
                $form->add('name', TextType::class, [
                    'data' => $this->parameterNames[$parameter->getName()]
                ]);
                break;
        }
    }
}