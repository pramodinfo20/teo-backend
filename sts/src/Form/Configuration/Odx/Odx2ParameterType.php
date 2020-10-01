<?php

namespace App\Form\Configuration\Odx;

use App\Enum\TypeRange;
use App\Form\EventListener\Configuration\Odx\FixProtocolListener;
use App\Form\EventListener\Configuration\Odx\FixSerialListener;
use App\Form\EventListener\Configuration\Odx\FixUdsIdProtocolListener;
use App\Form\Type\FloatType;
use App\Model\Configuration\Odx2Parameter;
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

class Odx2ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('odxSts02', HiddenType::class)
            ->add('parameterId', HiddenType::class)
            ->add('overwrittenValueSetId', HiddenType::class)
            ->add('order', HiddenType::class)
            ->add('name', HiddenType::class)
            ->add('odx1', HiddenType::class)
            ->add('protocol', HiddenType::class)
            ->add('udsId', HiddenType::class)
            ->add('read', HiddenType::class)
            ->add('write', HiddenType::class)
            ->add('confirm', HiddenType::class)
            ->add('variableType', HiddenType::class)
            ->add('coding', HiddenType::class)
            ->add('bigEndian', HiddenType::class)
            ->add('variableTypeId', HiddenType::class)
            ->add('bytes', HiddenType::class)
            ->add('factor', HiddenType::class)
            ->add('offset', HiddenType::class)
            ->add('unit', HiddenType::class)
            ->add('valueString', TextType::class)
            ->add('valueBlob', TextType::class)
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
            ->add('linkingType', HiddenType::class)
            ->add('valueHex', HiddenType::class)
            ->add('linkingTypeName', HiddenType::class)
            ->add('startBit', HiddenType::class)
            ->add('stopBit', HiddenType::class)
            ->add('headerProtocol', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('linkedValueName', TextType::class)
            ->addEventSubscriber(new FixSerialListener())
            ->addEventSubscriber(new FixProtocolListener())
            ->addEventSubscriber(new FixUdsIdProtocolListener());
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
            'data_class' => Odx2Parameter::class,
            /*
             * It's a global validation, with this case we can
             * set multiple fields to validate with their object such
             * like uds when need states of some another fields.
             */
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'Odx2ParameterType';
    }
}