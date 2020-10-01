<?php

namespace App\Form;

use App\Entity\GlobalParameters;
use App\Entity\Units;
use App\Entity\Users;
use App\Entity\VariableTypes;
use App\Enum\Entity\VariableTypes as variableTypesEnum;
use App\Form\EventListener\Parameters\FixMinMaxListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Doctrine\ORM\EntityRepository;


class GlobalParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('globalParameterName', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ]
            ])
            ->add('minValue', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('maxValue', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('globalUnit', EntityType::class, [
                'class' => Units::class,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('responsibleUser', EntityType::class, [
                'class' => Users::class,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('variableType', EntityType::class, [
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
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->addEventSubscriber(new FixMinMaxListener());
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => GlobalParameters::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['globalParameterName']])
            ]
        ]);
    }
}
