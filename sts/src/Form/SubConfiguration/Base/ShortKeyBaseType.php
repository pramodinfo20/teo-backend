<?php

namespace App\Form\SubConfiguration\Base;

use App\Entity\ConfigurationColors;
use App\Entity\ConfigurationEcus;
use App\Entity\Depots;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Enum\VinMethods;
use App\Form\EventListener\SubConfiguration\AddConfigurationColorsListener;
use App\Form\EventListener\SubConfiguration\AddEcuCheckboxListener;
use App\Form\EventListener\SubConfiguration\AddProductionDepotsListener;
use App\Model\ShortKeyModel;
use App\Model\ConfigurationI;
use App\Service\Vehicles\Configuration\SubConfiguration;
use App\Validator\Constraints\SubConfiguration\ShortKey\ShortKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\ShortKey\TypeYearSeriesShortKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use App\Validator\Constraints\SubConfiguration\TirePressure;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ShortKeyBaseType extends AbstractType
{
    private $subConfiguration;
    private $allOptions;
    private $allEcus;
    private $swMapping;

    /* Instead EntityType */
    private $placesOfProduction;
    private $allColors;


    public function __construct(SubConfiguration $subConfiguration, ObjectManager $manager)
    {
        $this->subConfiguration = $subConfiguration;
        $this->allOptions = array_map(function ($option)
        {
            return array_merge([' --- ' => null], $option);
        }
            , $subConfiguration->getAllOptions());
        $this->allEcus = $manager->getRepository(ConfigurationEcus::class)->findBy([], ['ecuName' => 'ASC']);
        $this->placesOfProduction = $manager->getRepository(Depots::class)->findBy(['depotType' => 1]);
        $this->allColors = $manager->getRepository(ConfigurationColors::class)->findAll();
        $this->swMapping = $manager->getrepository(EcuSwVersionSubVehicleConfigurationMapping::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subConfigurationId', HiddenType::class)
            ->add('type', TextType::class, [
                'attr' => [
                    'size' => 1
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(1),
                    new Type('alpha')
                ]
            ])
            ->add('year', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 99,
                    'class' => 'year'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('series', TextType::class, [
                'attr' => [
                    'size' => 2
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(2),
                    new Type('digit')
                ]
            ])
            ->add('layout', ChoiceType::class, [
                'choices' => $this->allOptions[ShortKeyModel::LAYOUT],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('feature', ChoiceType::class, [
                'choices' => $this->allOptions[ShortKeyModel::FEATURE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('battery', ChoiceType::class, [
                'choices' => $this->allOptions[ShortKeyModel::BATTERY],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('espPart', CheckboxType::class, [
            ])
            ->add('espPartReport', CheckboxType::class, [
            ])
            ->add('rotatingBacon', CheckboxType::class, [
            ])
            ->add('rotatingBaconReport', CheckboxType::class, [
            ])
            ->add('partAtCoDriverPosition', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('partAtCoDriverPositionReport', CheckboxType::class, [
            ])
            ->add('typeOfBattery', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('typeOfBatteryReport', CheckboxType::class, [
            ])
            ->add('radio', CheckboxType::class, [
            ])
            ->add('radioReport', CheckboxType::class, [
            ])
            ->add('isDeutschePostConfiguration', CheckboxType::class, [
            ])
            ->add('targetState', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('espFunctionality', CheckboxType::class, [
            ])
            ->add('tirePressFront', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new TirePressure(true)
                ],
                'attr' => [
                    'title' => 'Value must be between 200 and 990 kPa'
                ]
            ])
            ->add('tirePressRear', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new TirePressure(false)
                ],
                'attr' => [
                    'title' => 'Value must be between 200 and 990 kPa'
                ]
            ])
            ->add('comment', TextType::class, [])
            ->add('testSoftwareVersion', CheckboxType::class, [
            ])
            /* ---------------------------- OLD TABLE - SUPPORT ----------------------------------------------------- */
            ->add('vinMethod', ChoiceType::class, [
                'choices' => [
                  'SOP2017' => VinMethods::VIN_SOP2017,
                  'SOP2018' =>  VinMethods::VIN_SOP2018,
                  'EXT IMPORT' => VinMethods::VIN_EXT_IMPORT
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('chargerControllable', CheckboxType::class, [
                ])
            /* ------------------------------------------------------------------------------------------------------ */
            ->addEventSubscriber(new AddEcuCheckboxListener($builder, $this->allEcus, $this->swMapping))
            ->addEventSubscriber(new AddProductionDepotsListener($this->placesOfProduction))
            ->addEventSubscriber(new AddConfigurationColorsListener($this->allColors));
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
            'data_class' => ShortKeyModel::class,
            /*
              * It's a global validation, with this case we can
              * set multiple fields to validate with their object such
              * like uds when need states of some another fields.
             */
            'constraints' => [
                new TypeYearSeriesShortKey(),
                new ShortKeyQuestionMark(),
                new DeprecatedConfiguration(),
            ]
        ]);
    }
}