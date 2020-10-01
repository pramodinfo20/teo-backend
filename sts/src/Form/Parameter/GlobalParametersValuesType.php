<?php

namespace App\Form\Parameter;

use App\Form\EventListener\Parameters\FixGlobalValuesListener;
use App\Form\Type\FloatType;
use App\Model\Parameter\GlobalParameter;
use App\Validator\Constraints\Parameter\CoC\DoubleDecimalPoint;
use App\Validator\Constraints\Parameter\Range;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GlobalParametersValuesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', HiddenType::class)
            ->add('max', HiddenType::class)
            ->add('variableTypeId', HiddenType::class)
            ->add('valueString', TextType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueInteger', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueBigInteger', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueSigned', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueUnsigned', IntegerType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueDouble', FloatType::class, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new NotNull()
                ]
            ])
            ->add('valueBool', CheckboxType::class, [
                'label' => false,
                'constraints' => [

                ]
            ])
            ->add('valueDate', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
            ])
            ->addEventSubscriber(new FixGlobalValuesListener());

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => GlobalParameter::class,
            'constraints' => [
                new Range()
            ]
        ]);
    }
}
