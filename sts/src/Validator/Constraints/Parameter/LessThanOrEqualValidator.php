<?php

namespace App\Validator\Constraints\Parameter;

use App\Entity\GlobalParameters;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class LessThanOrEqualValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof LessThanOrEqual) {
            throw new UnexpectedTypeException($constraint, LessThanOrEqual::class);
        }

        if (!($parameter instanceof GlobalParameters)) {
            throw new UnexpectedValueException($parameter, GlobalParameters::class);
        }

        if ($parameter->getMinValue() != 0 && $parameter->getMaxValue() != 0) {
            if ($parameter->getMinValue() >= $parameter->getMaxValue()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('minValue')
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}