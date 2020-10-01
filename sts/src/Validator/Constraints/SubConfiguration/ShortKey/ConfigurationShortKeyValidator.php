<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 11:39 AM
 */

namespace App\Validator\Constraints\SubConfiguration\ShortKey;


use App\Entity\VehicleConfigurations;
use App\Model\ShortKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConfigurationShortKeyValidator extends ConstraintValidator
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
        if (!$constraint instanceof ConfigurationShortKey) {
            throw new UnexpectedTypeException($constraint, ConfigurationShortKey::class);
        }

        if (!($configuration instanceof ShortKeyModel)) {
            throw new UnexpectedValueException($configuration, ShortKeyModel::class);
        }

        $key = $this->subConfigurationService->generateShortKey($configuration);
        if (is_null($key)) {
            $this->context->buildViolation($constraint->message2)
                ->setTranslationDomain('validators')
                ->addViolation();
        } else {
            $configurations = $this->manager->getRepository(VehicleConfigurations::class)
                ->findDuplicatedShortKey($key, $configuration->getConfigurationId());

            if (!empty($configurations)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $key)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}