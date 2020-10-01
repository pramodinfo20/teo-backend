<?php

namespace App\Form\SubConfiguration;

use App\Entity\ConfigurationColors;
use App\Entity\ConfigurationEcus;
use App\Entity\Depots;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\ReleaseStatus;
use App\Form\EventListener\SubConfiguration\AddEcuCheckboxListener;
use App\Model\ShortKeyModel;
use App\Validator\Constraints\SubConfiguration\Release;
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
use Symfony\Component\Validator\Constraints\NotBlank;

class ShortKeyEditType extends AbstractType
{
    private $allEcus;
    private $swMapping;

    /* Instead EntityType */
    private $placesOfProduction;
    private $allColors;
    private $releaseStates;
    private $releases;


    public function __construct(ObjectManager $manager)
    {
        $this->allEcus = $manager->getRepository(ConfigurationEcus::class)->findBy([], ['ecuName' => 'ASC']);
        $this->placesOfProduction = $manager->getRepository(Depots::class)->findBy(['depotType' => 1]);
        $this->allColors = $manager->getRepository(ConfigurationColors::class)->findAll();
        $this->releaseStates = $manager->getRepository(ReleaseStatus::class)->findAll();
        $this->releases = [];
        foreach ($this->releaseStates as $release) {
            $this->releases[$release->getReleaseStatusName()] = $release->getReleaseStatusId();
        }
        $this->swMapping = $manager->getrepository(EcuSwVersionSubVehicleConfigurationMapping::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subConfigurationId', HiddenType::class)
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
            ->add('releaseState', ChoiceType::class, [
                'choices' => $this->releases,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->addEventSubscriber(new AddEcuCheckboxListener($builder, $this->allEcus, $this->swMapping));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => ShortKeyModel::class,
            'constraints' => [
                new Release(),
            ]
        ]);
    }
}