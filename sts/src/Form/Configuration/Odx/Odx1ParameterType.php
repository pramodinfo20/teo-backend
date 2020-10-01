<?php

namespace App\Form\Configuration\Odx;

use App\Enum\TypeRange;
use App\Form\EventListener\Configuration\Odx\FixDefaultLinkingTypeListener;
use App\Form\Type\FloatType;
use App\Model\Configuration\Odx1Parameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class Odx1ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('odxSts02', HiddenType::class)
            ->add('parameterId', HiddenType::class)
            ->add('overwrittenValueSetId', HiddenType::class)
            ->add('odx2', HiddenType::class)
            ->add('order', HiddenType::class)
            ->add('name', HiddenType::class)
            ->add('read', HiddenType::class)
            ->add('write', HiddenType::class)
            ->add('confirm', HiddenType::class)
            ->add('unit', HiddenType::class)
            ->add('variableType', HiddenType::class)
            ->add('variableTypeId', HiddenType::class)
            ->add('linkingType', HiddenType::class)
            ->add('linkingTypeId', HiddenType::class)
            ->add('valueHex', HiddenType::class)
            ->add('valueString', TextType::class)
            ->add('valueInteger', NumberType::class)
            ->add('valueUnsigned', NumberType::class, [
                'constraints' => [
                    new Range(
                        [
                            'min' => 0,
                            'max' => TypeRange::MAX_UNSIGNED_VALUE
                        ]
                    )
                    ]
            ])
            ->add('valueBool', CheckboxType::class)
            ->add('type', HiddenType::class);
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
            'data_class' => Odx1Parameter::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx1ParameterType';
    }
}