<?php

namespace App\Form\Odx;

use App\Model\Odx1Collection;
use App\Validator\Constraints\SameParameterNameForGlobal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Odx1ParameterCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameters', CollectionType::class, [
                'entry_type' => Odx1ParameterType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'attr' => [
                    'class' => 'odx1-table',
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
            'data_class' => Odx1Collection::class,
            'constraints' => [
                new SameParameterNameForGlobal()
            ],
            'ecu' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx1ParameterCollectionType';
    }
}