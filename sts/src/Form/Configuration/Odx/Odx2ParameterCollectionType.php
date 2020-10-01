<?php

namespace App\Form\Configuration\Odx;

use App\Model\Configuration\Odx2Collection;
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
                'attr' => [
                    'class' => 'odx2-table',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Odx2Collection::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx2ParameterCollectionType';
    }
}