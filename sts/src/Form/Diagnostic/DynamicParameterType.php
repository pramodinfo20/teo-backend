<?php

namespace App\Form\Diagnostic;

use App\Model\Diagnostic\DynamicParameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicParameterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameterId', IntegerType::class,
                [
                    'attr' => [
                        'readonly' => true,
                    ]
                ])
            ->add('value', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            /* It's important to set this due to new fields,
             * listeners and subscribers are not present at the
             * beginning of creation form and form validate extra
             * fields as a threat, do not turn it on when it's not necessary!
             */
            'allow_extra_fields' => true,
            'data_class' => DynamicParameter::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DynamicParameterType';
    }
}