<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 11:39 AM
 */

namespace App\Validator\Constraints\SubConfiguration\ShortKey;


use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Model\ShortKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ShortKeyValidator extends ConstraintValidator
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
        if (!$constraint instanceof ShortKey) {
            throw new UnexpectedTypeException($constraint, ShortKey::class);
        }

        if (!($subConfiguration instanceof ShortKeyModel)) {
            throw new UnexpectedValueException($subConfiguration, ShortKeyModel::class);
        }

        $key = $this->subConfigurationService->generateShortKey($subConfiguration);
        if (is_null($key)) {
            $this->context->buildViolation($constraint->message2)
                ->setTranslationDomain('validators')
                ->addViolation();
        } else {
            $subConfigurations = $this->manager->getRepository(VehicleConfigurations::class)
                ->findDuplicatedShortKey($key,
                $this->manager->getRepository(SubVehicleConfigurations::class)->findOneBy(['subVehicleConfigurationId' =>
                    $subConfiguration->getSubConfigurationId()])->getVehicleConfiguration()->getVehicleConfigurationId());
            if (!empty($subConfigurations)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $key)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}