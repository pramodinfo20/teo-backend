<?php

namespace App\Validator\Constraints\Parameter\CoC;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DoubleDecimalPointValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DoubleDecimalPoint) {
            throw new UnexpectedTypeException($constraint, DoubleDecimalPoint::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (preg_match('/.*,.*/', $value))
            $this->buildValidationMessage('valueDouble', $constraint->message);

    }


    /**
     * @param $propertyName
     * @param $message
     */
    private
    function buildValidationMessage($propertyName, $message)
    {
        $this->context
            ->buildViolation($message)
            ->atPath($propertyName)
            ->setTranslationDomain('validators')
            ->addViolation();
    }
}