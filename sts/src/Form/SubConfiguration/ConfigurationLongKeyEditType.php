<?php


namespace App\Form\SubConfiguration;


use App\Entity\Depots;
use App\Enum\VinMethods;
use App\Form\EventListener\SubConfiguration\AddProductionDepotsListener;
use App\Model\LongKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use App\Validator\Constraints\SubConfiguration\LongKey\ConfigurationLongKey;
use App\Validator\Constraints\SubConfiguration\LongKey\InvalidLongKeyToFix;
use App\Validator\Constraints\SubConfiguration\LongKey\LongKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\LongKey\TypeYearSeriesLongKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use http\Exception\InvalidArgumentException;
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

class ConfigurationLongKeyEditType extends AbstractType
{
    private $subConfiguration;
    private $allOptions;

    /* Instead EntityType */
    private $placesOfProduction;

    public function __construct(SubConfiguration $subConfiguration, ObjectManager $manager)
    {
        $this->subConfiguration = $subConfiguration;
        $this->allOptions = array_map(function ($option) {
            return array_merge([' --- ' => null], $option);
        }
            ,$subConfiguration->getAllOptions());
        $this->placesOfProduction = $manager->getRepository(Depots::class)->findBy(['depotType' => 1]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('configurationId', HiddenType::class, [])
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
            ->add('customerKey', TextType::class, [
                'constraints' => [
                    new Type('alpha')
                ]
            ])
            ->add('devStatus', ChoiceType::class, [
                'choices' => $this->allOptions[LongKeyModel::DEV_STATUS],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('body', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::BODY],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('numberDrive', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::NUMBER_DRIVE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('engineType', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::ENGINE_TYPE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('stageOfCompletion', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::STAGE_OF_COMPLETION],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('bodyLength', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::BODY_LENGTH],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('frontAxle', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::FRONT_AXLE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('rearAxle', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::REAR_AXLE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('zgg', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::ZGG],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('typeOfFuel', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::TYPE_OF_FUEL],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('tractionBattery', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::TRACTION_BATTERY],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('chargingSystem', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::CHARGING_SYSTEM],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('vMax', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::VMAX],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('seats', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::SEATS],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('trailerHitch', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::TRAILER_HITCH],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('superstructures', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::SUPERSTRUCTURES],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('energySupplySuperStructure', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::ENERGY_SUPPLY_SUPERSTRUCTURE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('steering', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::STEERING],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('rearWindow', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::REAR_WINDOW],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('airConditioning', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::AIR_CONDITIONING],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('passengerAirbag', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::PASSENGER_AIRBAG],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('keyless', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::KEYLESS],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('specialApplicationArea', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::SPECIAL_APPLICATION_AREA],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('radio', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::RADIO],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('soundGenerator', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::SOUND_GENERATOR],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('countryCode', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::COUNTRY_CODE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('color', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::COLOR],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('wheeling', ChoiceType::class, [
                'choices' =>  $this->allOptions[LongKeyModel::WHEELING],
                'constraints' => [
                    new NotBlank()
                ]
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
            ->addEventSubscriber(new AddProductionDepotsListener($this->placesOfProduction))
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
            'data_class' => LongKeyModel::class,
            /*
               * It's a global validation, with this case we can
               * set multiple fields to validate with their object such
               * like uds when need states of some another fields.
             */

            'constraints' => [
                new TypeYearSeriesLongKey(),
                new LongKeyQuestionMark(),
                new ConfigurationLongKey(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}