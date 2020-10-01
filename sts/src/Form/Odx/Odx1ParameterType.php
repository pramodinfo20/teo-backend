<?php

namespace App\Form\Odx;

use App\Entity\CocParameters;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuSoftwareParameterNames;
use App\Entity\GlobalParameters;
use App\Entity\Units;
use App\Entity\VariableTypes;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;
use App\Enum\Parameter as ParameterEnum;
use App\Enum\TypeRange;
use App\Form\EventListener\Parameters\Odx\FixCopyStsPartNumberListener;
use App\Form\EventListener\Parameters\Odx\FixLinkingTypeListener;
use App\Form\EventListener\Parameters\Odx1\FixValueTypeListenerOdx1;
use App\Form\EventListener\Parameters\Odx1\FixDisabledVariableTypeListener;
use App\Form\EventListener\Parameters\Odx1\FixNamesListener;
use App\Form\EventListener\Parameters\Odx2\FixConstantNamesListener;
use App\Form\Type\FloatType;
use App\Model\Odx1Parameter;
use App\Utils\Dictionary;
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

class Odx1ParameterType extends AbstractType
{
    /**
     * @var EcuSoftwareParameterNames[]
     */
    private $parametersNames;

    /**
     * @var VariableTypes[]
     */
    private $variableTypes;

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    public function __construct(EntityManagerInterface $entityManager,  TranslatorInterface $translator)
    {
        $this->translator = $translator;

        $this->parametersNames = Dictionary::transformToDictionary(
            $entityManager->getRepository(EcuSoftwareParameterNames::class)->findAll(),
            'ecuSoftwareParameterName'
        );

        $this->variableTypes = Dictionary::transformToDictionary(
            $entityManager->getRepository(VariableTypes::class)->findAll(),
            'variableTypeName'
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('odxSts02', HiddenType::class)
            ->add('parameterId', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('odx2', HiddenType::class)
            ->add('read', CheckboxType::class, [
                'disabled' => true
            ])
            ->add('write', CheckboxType::class, [
                'disabled' => true
            ])
            ->add('confirm', CheckboxType::class, [
                'disabled' => true
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
                'disabled' => true
            ])
            ->add('unit', EntityType::class, [
                'class' => Units::class,
                'query_builder' => function (EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('u');
                },
                'disabled' => true
            ])
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
            ->add('linkedToGlobalParameter', EntityType::class, [
                'class' => GlobalParameters::class,
                'query_builder' => function (EntityRepository $repository) use ($options)
                {
                    return $repository->createQueryBuilder('g')
                        ->where('g.specialEcu =:ae')
                        ->orWhere('g.specialEcu is null')
                        ->setParameter('ae', $options['ecu']);
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
            ->add('linkingType', ChoiceType::class, [
                'choices' => [
                    'choices.odx.types.default' => ParameterEnum::LINKING_TYPE_DEFAULT,
                    'choices.odx.types.constant' => ParameterEnum::LINKING_TYPE_CONSTANT,
                    'choices.odx.types.global' => ParameterEnum::LINKING_TYPE_GLOBAL_PARAMETER,
                    'choices.odx.types.dynamic' => ParameterEnum::LINKING_TYPE_DYNAMIC_VALUE,
                    /*'choices.odx.types.coc' => ParameterEnum::LINKING_TYPE_COC_PARAMETER*/
                ],
                'choice_translation_domain' => 'forms',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->addEventSubscriber(new FixConstantNamesListener($this->parametersNames))
            ->addEventSubscriber(new FixLinkingTypeListener($this->translator))
            ->addEventSubscriber(new FixCopyStsPartNumberListener())
            ->addEventSubscriber(new FixNamesListener())
            ->addEventSubscriber(new FixDisabledVariableTypeListener($this->variableTypes))
            ->addEventSubscriber(new FixValueTypeListenerOdx1())
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
            'data_class' => Odx1Parameter::class,
            'ecu' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Odx1ParameterType';
    }
}