<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WindchillValidator extends ConstraintValidator
{
    const LOCAL_WINDCHILL_URL = 'http://windchillapp.streetscooter.local/Windchill';
    const REMOTE_WINDCHILL_URL = 'http://windchill.streetscooter.eu/Windchill';

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Windchill) {
            throw new UnexpectedTypeException($constraint, Windchill::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');
        }

        if (substr($value, 0, strlen(self::LOCAL_WINDCHILL_URL)) == self::LOCAL_WINDCHILL_URL
            || substr($value, 0, strlen(self::REMOTE_WINDCHILL_URL)) == self::REMOTE_WINDCHILL_URL) {
            return true;
        } else {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%url%', $value)
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}