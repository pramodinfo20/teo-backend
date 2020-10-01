<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UdsIdValueValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof UdsIdValue) {
            throw new UnexpectedTypeException($constraint, UdsIdValue::class);
        }

        if (null === $parameter || '' === $parameter) {
            return;
        }

        $udsIdValue = $parameter;
        if (substr(strtolower($parameter), 0, 2) == '0x') {
            $udsIdValue = substr($parameter, 2);
        }

        if (!ctype_xdigit($udsIdValue)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('validators')
                ->addViolation();
        }

        if (strlen($udsIdValue) != 4) {
            $this->context->buildViolation($constraint->message2)
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}