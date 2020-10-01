<?php

namespace App\Validator\Constraints\SubConfiguration;

use App\Model\ConfigurationI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;


class DeprecatedConfigurationValidator extends ConstraintValidator
{
    public function validate($subConfiguration, Constraint $constraint): void
    {
        if (!$constraint instanceof DeprecatedConfiguration) {
            throw new UnexpectedTypeException($constraint, DeprecatedConfiguration::class);
        }

        if (!($subConfiguration instanceof ConfigurationI)) {
            throw new UnexpectedValueException($subConfiguration, ConfigurationI::class);
        }

        $type = strtoupper($subConfiguration->getType());
        $year = $subConfiguration->getYear();
        $series = $subConfiguration->getSeries();

        if ($type == 'D' or $type == 'B') {
            if ($year < 16) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setTranslationDomain('validators')
                    ->addViolation();

            } elseif ($year == 16) {
                if ($series < 3) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }
            }
        }
    }
}