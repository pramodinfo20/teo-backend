<?php

namespace App\Validator\Constraints\SubConfiguration\LongKey;


use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Model\LongKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class InvalidLongKeyToFixValidator extends ConstraintValidator
{
    private $manager;
    private $subConfigurationService;

    public function __construct(ObjectManager $manager, SubConfiguration $subConfigurationService)
    {
        $this->manager = $manager;
        $this->subConfigurationService = $subConfigurationService;
    }

    public function validate($subConfiguration, Constraint $constraint): void
    {
        if (!$constraint instanceof InvalidLongKeyToFix) {
            throw new UnexpectedTypeException($constraint, InvalidLongKey::class);
        }

        if (!($subConfiguration instanceof LongKeyModel)) {
            throw new UnexpectedValueException($subConfiguration, LongKeyModel::class);
        }

        $key = $this->subConfigurationService->generateLongKey($subConfiguration);

        if (is_null($key)) {
            $this->context->buildViolation($constraint->message2)
                ->setTranslationDomain('validators')
                ->addViolation();
        } else {
            $subConfigurations = $this->manager->getRepository(VehicleConfigurations::class)
                ->findDuplicatedInvalidLongKeyToFix($this->manager->getRepository(SubVehicleConfigurations::class)
                    ->findOneBy(['subVehicleConfigurationId' =>
                    $subConfiguration->getSubConfigurationId()])->getVehicleConfiguration()->getVehicleConfigurationId()
                    ,$key, $subConfiguration->getCustomerKey());

            if (!empty($subConfigurations)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $key)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}