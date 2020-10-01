<?php


namespace App\Validator\Constraints\SubConfiguration;

use http\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


class TirePressureValidator extends ConstraintValidator
{
    const MIN_ALLOWED_PRESSURE = 200;
    const MAX_ALLOWED_PRESSURE = 990;

    /**
     * Checks if the passed value is valid.
     *
     * @param $value
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TirePressure) {
            throw new UnexpectedTypeException($constraint, TirePressure::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_int($value)) {
            throw new UnexpectedValueException($value, 'integer');
        }

        if ($value < self::MIN_ALLOWED_PRESSURE || $value > self::MAX_ALLOWED_PRESSURE) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameters([
                    '%min%' => self::MIN_ALLOWED_PRESSURE,
                    '%max%' => self::MAX_ALLOWED_PRESSURE
                ])
                ->setTranslationDomain('validators')
                ->addViolation();
        }


    }
}