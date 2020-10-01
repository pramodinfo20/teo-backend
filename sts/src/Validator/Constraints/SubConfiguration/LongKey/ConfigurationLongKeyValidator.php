<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 11:39 AM
 */

namespace App\Validator\Constraints\SubConfiguration\LongKey;

use App\Entity\VehicleConfigurations;
use App\Model\LongKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConfigurationLongKeyValidator extends ConstraintValidator
{
    private $manager;
    private $subConfigurationService;

    public function __construct(ObjectManager $manager, SubConfiguration $subConfigurationService)
    {
        $this->manager = $manager;
        $this->subConfigurationService = $subConfigurationService;
    }

    public function validate($configuration, Constraint $constraint): void
    {
        if (!$constraint instanceof ConfigurationLongKey) {
            throw new UnexpectedTypeException($constraint, ConfigurationLongKey::class);
        }

        if (!($configuration instanceof LongKeyModel)) {
            throw new UnexpectedValueException($configuration, LongKeyModel::class);
        }

        $key = $this->subConfigurationService->generateLongKey($configuration);

        if (is_null($key)) {
            $this->context->buildViolation($constraint->message2)
                ->setTranslationDomain('validators')
                ->addViolation();
        } else {
            $configurations = $this->manager->getRepository(VehicleConfigurations::class)
                ->findDuplicatedLongKey($key, $configuration->getCustomerKey());

            $configurations1 = $this->manager->getRepository(VehicleConfigurations::class)
                ->findDuplicatedInvalidLongKeyToFix($configuration->getConfigurationId(), $key,
                    $configuration->getCustomerKey());
            if (!empty($configurations) || !empty($configurations1)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $key)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}