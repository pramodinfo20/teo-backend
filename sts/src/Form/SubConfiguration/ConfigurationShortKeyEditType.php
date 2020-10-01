<?php


namespace App\Form\SubConfiguration;


use App\Entity\ConfigurationColors;
use App\Entity\Depots;
use App\Enum\VinMethods;
use App\Form\EventListener\SubConfiguration\AddConfigurationColorsListener;
use App\Form\EventListener\SubConfiguration\AddProductionDepotsListener;
use App\Model\ShortKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use App\Validator\Constraints\SubConfiguration\ShortKey\ConfigurationShortKey;
use App\Validator\Constraints\SubConfiguration\ShortKey\ShortKeyQuestionMark;
use App\Validator\Constraints\SubConfiguration\ShortKey\TypeYearSeriesShortKey;
use App\Validator\Constraints\SubConfiguration\DeprecatedConfiguration;
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

class ConfigurationShortKeyEditType extends AbstractType
{
    private $subConfiguration;
    private $allOptions;

    /* Instead EntityType */
    private $placesOfProduction;
    private $allColors;


    public function __construct(SubConfiguration $subConfiguration, ObjectManager $manager)
    {
        $this->subConfiguration = $subConfiguration;
        $this->allOptions = array_map(function ($option) {
            return array_merge([' --- ' => null], $option);
        }
            ,$subConfiguration->getAllOptions());
        $this->placesOfProduction = $manager->getRepository(Depots::class)->findBy(['depotType' => 1]);
        $this->allColors = $manager->getRepository(ConfigurationColors::class)->findAll();
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
            ->add('layout', ChoiceType::class, [
                'choices' => $this->allOptions[ShortKeyModel::LAYOUT],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('feature', ChoiceType::class, [
                'choices' =>  $this->allOptions[ShortKeyModel::FEATURE],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('battery', ChoiceType::class, [
                'choices' =>  $this->allOptions[ShortKeyModel::BATTERY],
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
            ->addEventSubscriber(new AddConfigurationColorsListener($this->allColors))
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
            'data_class' => ShortKeyModel::class,
            /*
              * It's a global validation, with this case we can
              * set multiple fields to validate with their object such
              * like uds when need states of some another fields.
             */
            'constraints' => [
                new TypeYearSeriesShortKey(),
                new ShortKeyQuestionMark(),
                new ConfigurationShortKey(),
                new DeprecatedConfiguration()
            ]
        ]);
    }
}