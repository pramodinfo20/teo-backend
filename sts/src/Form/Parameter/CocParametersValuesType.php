<?php

namespace App\Form\Parameter;

use App\Form\EventListener\Parameters\FixCocValuesListener;
use App\Form\Type\FloatType;
use App\Model\Parameter\CocParameter;
use App\Validator\Constraints\Parameter\CoC\DoubleDecimalPoint;
use App\Validator\Constraints\Parameter\CoC\IntegerValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CocParametersValuesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('variableTypeId', HiddenType::class)
            ->add('cocParameterValueSetId', HiddenType::class)
            ->add('cocParameterId', HiddenType::class)
            ->add('cocParameterName', HiddenType::class)
            ->add('counter', HiddenType::class)
            ->add('description', HiddenType::class)
            ->add('field', HiddenType::class)
            ->add('unitName', HiddenType::class)
            ->add('valueString', TextType::class, [
                'label' => false,
            ])
            ->add('valueDate', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
            ])
            ->add('valueDouble', FloatType::class, [
                'label' => false,
                'constraints' => [
                    new DoubleDecimalPoint()
                ]
            ])
            ->add('valueInteger', IntegerType::class, [
                'label' => false,
            ])
            ->add('valueBigInteger', IntegerType::class, [
                'label' => false,
            ])
            ->add('valueBool', CheckboxType::class, [
                'label' => false,
            ])
            ->add('variableTypeName', HiddenType::class)
            ->add('responsibleUser', HiddenType::class)
            ->addEventSubscriber(new FixCocValuesListener());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => false,
            'data_class' => CocParameter::class,
            'constraints' => [
                new IntegerValue(),
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CocParametersValuesType';
    }
}
