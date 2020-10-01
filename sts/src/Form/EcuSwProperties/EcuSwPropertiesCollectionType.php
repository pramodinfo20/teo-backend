<?php

namespace App\Form\EcuSwProperties;

use App\Model\EcuSwProperties\EcuSwPropertiesCollection;
use App\Validator\Constraints\EcuSwProperties\ExistingPropertyNameValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcuSwPropertiesCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('properties', CollectionType::class, [
                'entry_type' => EcuSwPropertiesModelType::class,
                'allow_add' => true,
                'allow_delete' => false,
                'prototype' => true,
                'attr' => [
                    'class' => 'properties-table',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => EcuSwPropertiesCollection::class,
            'constraints' => [
                new ExistingPropertyNameValue()
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'EcuSwPropertiesCollectionType';
    }

}