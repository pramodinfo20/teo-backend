<?php

namespace App\Form\Parameter;

use App\Entity\ConfigurationEcus;
use App\Entity\Ecus;
use App\Entity\GlobalParameters;
use App\Entity\Units;
use App\Entity\Users;
use App\Entity\VariableTypes;
use App\Enum\Entity\VariableTypes as variableTypesEnum;
use App\Form\EventListener\Parameters\FixMinMaxListener;
use App\Validator\Constraints\Parameter\LessThanOrEqual;
use App\Validator\Constraints\Parameter\MinMaxPropertyNotEmpty;
use App\Validator\Constraints\Parameter\MinMaxPropertyNumeric;
use App\Validator\Constraints\Parameter\MinMaxPropertyValue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GlobalParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('globalParameterName', TextType::class, [
                'label' => 'label.parameter.globalParametersType.globalParameterName',
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
            ->add('minValue', TextType::class)
            ->add('maxValue', TextType::class)
            ->add('globalUnit', EntityType::class, [
                'class' => Units::class,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('globalUnit')
                        ->orderBy('globalUnit.unitName', 'ASC');
                }
            ])
            ->add('specialEcu', EntityType::class, [
                'class' => ConfigurationEcus::class,
                'choice_label' => 'ecuName',
                'required' => false
            ])
            ->add('chargingControlRelated', ChoiceType::class, [
                'choices' => [
                    'true' => true,
                    'false' => false
                ]
            ])
            ->add('responsibleUser', EntityType::class, [
                'class' => Users::class,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->addEventSubscriber(new FixMinMaxListener());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => GlobalParameters::class,
            'constraints' => [
                new MinMaxPropertyNumeric(),
                new MinMaxPropertyNotEmpty(),
                new LessThanOrEqual(),
                new MinMaxPropertyValue(),
                new UniqueEntity(['fields' => ['globalParameterName']])
            ],
            'translation_domain' => 'forms'
        ]);
    }
}
