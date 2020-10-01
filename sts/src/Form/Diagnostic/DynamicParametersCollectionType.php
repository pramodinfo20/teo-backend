<?php

namespace App\Form\Diagnostic;

use App\Validator\Constraints\Diagnostic;
use App\Form\Diagnostic\DynamicParameterType;
use App\Model\Diagnostic\DynamicParametersCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicParametersCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameters', CollectionType::class, [
                'entry_type' => DynamicParameterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'dynamic-parameters-table',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => DynamicParametersCollection::class,
            'constraints' => [
                new Diagnostic\LinkedParameter()
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DynamicParametersCollectionType';
    }
}