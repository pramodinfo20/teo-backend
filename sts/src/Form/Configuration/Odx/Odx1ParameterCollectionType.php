<?php

namespace App\Form\Configuration\Odx;

use App\Model\Configuration\Odx1Collection;
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Odx1Collection::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx1ParameterCollectionType';
    }
}