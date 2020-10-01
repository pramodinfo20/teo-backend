<?php

namespace App\Validator\Constraints\SubConfiguration;

use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\ReleaseStatus;
use App\Model\ConfigurationI;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ReleaseValidator extends ConstraintValidator
{
    private $manager;
    private $subConfigurationService;

    public function __construct(ObjectManager $manager, SubConfiguration $subConfigurationService)
    {
        $this->manager = $manager;
        $this->subConfigurationService = $subConfigurationService;
    }

    public function validate($subConfigurationModel, Constraint $constraint): void
    {
        if (!$constraint instanceof Release) {
            throw new UnexpectedTypeException($constraint, Release::class);
        }

        if ($subConfigurationModel->getReleaseState() == ReleaseStatus::RELEASE_STATUS_RELEASED) {
            $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
                ->findOneBy(['subVehicleConfigurationId' => $subConfigurationModel->getSubConfigurationId()]);

            if (!$this->subConfigurationService->checkAssignedSoftwares($subConfiguration)) {
                $this->context->buildViolation($constraint->assignedSoftware)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }

            if (!$this->subConfigurationService->checkReleasedSoftwares($subConfiguration)) {
                $this->context->buildViolation($constraint->releasedSoftware)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }

            if (!$this->subConfigurationService->checkAssignedGlobalsValues($subConfiguration)) {
                $this->context->buildViolation($constraint->assignedGlobalsWithValues)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}