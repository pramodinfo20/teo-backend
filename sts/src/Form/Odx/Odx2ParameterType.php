<?php

namespace App\Form\Odx;

use App\Entity\CocParameters;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSoftwareParameterNames;
use App\Entity\GlobalParameters;
use App\Entity\Units;
use App\Entity\VariableTypes;
use App\Enum\Entity\EcuCommunicationProtocols as EcuCommunicationProtocolsEnum;
use App\Enum\Parameter as ParameterEnum;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;
use App\Enum\TypeRange;
use App\Form\EventListener\Parameters\Odx\FixCopyStsPartNumberListener;
use App\Form\EventListener\Parameters\Odx\FixLinkingTypeListener;
use App\Form\EventListener\Parameters\Odx\FixUnitsListener;
use App\Form\EventListener\Parameters\Odx\FixVariableTypeListener;
use App\Form\EventListener\Parameters\Odx2\FixActivatedListener;
use App\Form\EventListener\Parameters\Odx2\FixConstantNamesListener;
use App\Form\EventListener\Parameters\Odx2\FixFactorOffsetUnitListener;
use App\Form\EventListener\Parameters\Odx2\FixProtocolListener;
use App\Form\EventListener\Parameters\Odx2\FixSerialListener;
use App\Form\EventListener\Parameters\Odx2\FixUdsIdProtocolListener;
use App\Form\EventListener\Parameters\Odx2\FixValuesListener;
use App\Form\EventListener\Parameters\Odx2\FixValueTypeListenerOdx2;
use App\Form\Type\FloatType;
use App\Model\Odx2Parameter;
use App\Utils\Dictionary;
use App\Validator\Constraints\UdsIdValue;
use App\Validator\Constraints\UdsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Contracts\Translation\TranslatorInterface;

class Odx2ParameterType extends AbstractType
{
    /**
     * @var VariableTypes[]
     */
    private $variableTypes;

    /**
     * @var Units[]
     */
    private $units;

    /**
     * @var EcuSoftwareParameterNames[]
     */
    private $parametersNames;

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->translator = $translator;

        $this->variableTypes = Dictionary::transformToDictionary(
            $entityManager->getRepository(VariableTypes::class)->findAll(),
            'variableTypeName'
        );

        $this->units = Dictionary::transformToDictionary(
            $entityManager->getRepository(Units::class)->findAll(),
            'unitName'
        );

        $this->parametersNames = Dictionary::transformToDictionary(
            $entityManager->getRepository(EcuSoftwareParameterNames::class)->findAll(),
            'ecuSoftwareParameterName'
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('odxSts02', HiddenType::class)
            ->add('parameterId', HiddenType::class)
            ->add('activated', CheckboxType::class)
            ->add('type', HiddenType::class, [
                'attr' => [
                    'class' => 'type'
                ]
            ])
            // new column coding
            ->add('coding', TextType::class, [
                'attr' => [
                    'size' => 10
                ]
            ])
            // new column big endian
            ->add('bigEndian', CheckboxType::class)
            ->add('name', TextType::class, [
                'attr' => [
                    'size' => 40
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('nameId', HiddenType::class)
            ->add('odx1', HiddenType::class)
            ->add('headerProtocol', HiddenType::class)
            ->add('read', CheckboxType::class, [
                'attr' => [
                    'class' => 'readRight'
                ]
            ])
            //ToDo: Fix Validation for selected protocols
            ->add('protocol', EntityType::class, [
                'class' => EcuCommunicationProtocols::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('p')
                        ->where('p.ecuCommunicationProtocolId IN (:ids)')
                        ->setParameter('ids', [
                            EcuCommunicationProtocolsEnum::ECU_COMMUNICATION_PROTOCOL_UDS,
                            EcuCommunicationProtocolsEnum::ECU_COMMUNICATION_PROTOCOL_XCP
                        ]);
                }
            ])
            ->add('udsId', TextType::class, [
                'attr' => [
                    'style' => 'width: 50px'
                ],
                //ToDo: Fix Validation for selected protocols
                'constraints' => [
                    new NotBlank(),
                    new UdsIdValue()
                ]
            ])
            ->add('write', CheckboxType::class, [
                'attr' => [
                    'class' => 'writeRight'
                ]
            ])
            ->add('confirm', CheckboxType::class, [
                'attr' => [
                    'class' => 'confirmRight'
                ]
            ])
            ->add('variableType', EntityType::class, [
                'class' => VariableTypes::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('v')
                        ->where('v.variableTypeId NOT IN (:ids)')
                        ->setParameter('ids', [
                            VariableTypesEnum::VARIABLE_TYPE_DOUBLE,
                            VariableTypesEnum::VARIABLE_TYPE_DATE
                        ]);
                },
                'constraints' => new NotBlank()
            ])
            ->add('bytes', IntegerType::class, [
                'attr' => [
                    'style' => 'width: 50px'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('factor', FloatType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('offset', FloatType::class, [
                'constraints' => [
                ]
            ])
            ->add('unit', EntityType::class, [
                'class' => Units::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('u');
                },
            ])
            ->add('valueString', TextType::class)
            ->add('valueBlob', TextType::class)
            ->add('valueInteger', IntegerType::class, [
                'constraints' => [
                    new Range(
                        [
                            'min' => TypeRange::MIN_INTEGER_VALUE,
                            'max' => TypeRange::MAX_INTEGER_VALUE
                        ]
                    )
                ]
            ])
            ->add('valueUnsigned', IntegerType::class, [
                'constraints' => [
                    new Range(
                        [
                            'min' => TypeRange::MIN_UNSIGNED_VALUE,
                            'max' => TypeRange::MAX_UNSIGNED_VALUE
                        ]
                    )
                ]
            ])
            ->add('valueBool', CheckboxType::class, [
                'label' => 'Is true',
            ])
            ->add('linkingType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('choices.eventListener.odx.types.default', [], 'forms')  => ParameterEnum::LINKING_TYPE_DEFAULT,
                    $this->translator->trans('choices.eventListener.odx.types.constant', [], 'forms') => ParameterEnum::LINKING_TYPE_CONSTANT,
                    $this->translator->trans('choices.eventListener.odx.types.global', [], 'forms') => ParameterEnum::LINKING_TYPE_GLOBAL_PARAMETER,
                    $this->translator->trans('choices.eventListener.odx.types.dynamic', [], 'forms') => ParameterEnum::LINKING_TYPE_DYNAMIC_VALUE,
                    /*$this->translator->trans( 'choices.eventListener.odx.types.coc', [], 'forms') => ParameterEnum::LINKING_TYPE_COC_PARAMETER*/
                ],
                'choice_translation_domain' => 'forms',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('startBit', IntegerType::class, [
                'attr' => [
                    'style' => 'width: 50px'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('stopBit', IntegerType::class, [
                'attr' => [
                    'style' => 'width: 50px'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('linkedToGlobalParameter', EntityType::class, [
                'class' => GlobalParameters::class,
                'query_builder' => function (EntityRepository $repository) use ($options)
                {
                    $repo = $repository->createQueryBuilder('g')
                        ->where('g.specialEcu =:ae')
                        ->orWhere('g.specialEcu is null')
                        ->setParameter('ae', $options['ecu']);
                    return $repo;
                },
            ])
            ->add('dynamicParameterValuesByDiagnosticSoftware', EntityType::class, [
                'class' => DynamicParameterValuesByDiagnosticSoftware::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('d');
                },
            ])
            /*->add('linkedToCocParameter', EntityType::class, [
                'class' => CocParameters::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('c');
                },
            ]) */
            ->addEventSubscriber(new FixConstantNamesListener($this->parametersNames))
            ->addEventSubscriber(new FixSerialListener())
            ->addEventSubscriber(new FixProtocolListener())
            ->addEventSubscriber(new FixLinkingTypeListener($this->translator))
            ->addEventSubscriber(new FixCopyStsPartNumberListener())
            ->addEventSubscriber(new FixVariableTypeListener($this->variableTypes))
            ->addEventSubscriber(new FixUnitsListener($this->units))
            ->addEventSubscriber(new FixFactorOffsetUnitListener())
//            ->addEventSubscriber(new FixActivatedListener())
            ->addEventSubscriber(new FixUdsIdProtocolListener())
            ->addEventSubscriber(new FixValueTypeListenerOdx2())
            ->addEventSubscriber(new FixValuesListener())
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
            'data_class' => Odx2Parameter::class,
            /*
             * It's a global validation, with this case we can
             * set multiple fields to validate with their object such
             * like uds when need states of some another fields.
             */
            'constraints' => [
                new UdsMessage()
            ],
            'ecu' => null
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'Odx2ParameterType';
    }
}