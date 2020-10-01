<?php

namespace App\Form\Odx;

use App\Model\Odx2Collection;
use App\Validator\Constraints\SameParameterNameForGlobal;
use App\Validator\Constraints\SameParameterName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Odx2ParameterCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameters', CollectionType::class, [
                'entry_type' => Odx2ParameterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'odx2-table',
                ],
                'entry_options' => [
                    'ecu' => $options['ecu']
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Odx2Collection::class,
            'constraints' => [
                new SameParameterNameForGlobal(),
                new SameParameterName()
            ],
            'ecu' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx2ParameterCollectionType';
    }
}