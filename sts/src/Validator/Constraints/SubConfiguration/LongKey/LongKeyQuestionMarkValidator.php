<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 11:39 AM
 */

namespace App\Validator\Constraints\SubConfiguration\LongKey;


use App\Model\LongKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class LongKeyQuestionMarkValidator extends ConstraintValidator
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
        if (!$constraint instanceof LongKeyQuestionMark) {
            throw new UnexpectedTypeException($constraint, LongKey::class);
        }

        if (!($subConfiguration instanceof LongKeyModel)) {
            throw new UnexpectedValueException($subConfiguration, LongKeyModel::class);
        }

        $key = $this->subConfigurationService->generateLongKey($subConfiguration);
        if (is_null($key)) {
            $this->context->buildViolation($constraint->message2)
                ->addViolation();
        } else {
            if (strpos($key, '?') !== false) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $key)
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}