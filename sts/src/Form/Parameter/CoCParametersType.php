<?php

namespace App\Form\Parameter;

use App\Entity\CocParameters;
use App\Entity\Units;
use App\Entity\Users;
use App\Entity\VariableTypes;
use App\Validator\Constraints\Parameter\CoC\CocParameterField;
use App\Validator\Constraints\Parameter\CoC\Management\FieldPropertyValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class CoCParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cocParameterName', TextType::class, [
                'label' => 'label.parameter.cocParametersType.cocParameterName',
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.parameter.cocParametersType.description',
            ])
            ->add('section', TextType::class, [
                'label' => 'label.parameter.cocParametersType.section',
            ])
            ->add('field', TextType::class, [
                'label' => 'label.parameter.cocParametersType.field',
                'constraints' => new CocParameterField()
            ])
            ->add('parameterOrder', IntegerType::class, [
                'label' => 'label.parameter.cocParametersType.order',
            ])
            ->add('variableType', EntityType::class, [
                'class' => VariableTypes::class,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('unit', EntityType::class, [
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => CocParameters::class,
            'translation_domain' => 'forms',
            'constraints' => [
                new UniqueEntity(['fields' => ['cocParameterName']])
            ],
        ]);
    }
}
