<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StsVersionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof StsVersion) {
            throw new UnexpectedTypeException($constraint, StsVersion::class);
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

        $versionLimits = ['<c1' => 'A', '>c1' => 'E', '<c23' => 12, '>c23' => date('y') + 1, '!=c4' => 'X',
            'regex' => [
                '/^[B-Z][0-9]{2}X[0-9A-F]{8}_[0-9]{2}\s*[A-Z]?$/',
                '/^[A-D]1[0-9]X[0-9]{6}_[0-9]{2}\s*[A-Z]?[.]?[0-9]*$/',
                '/^A12X825300_[0-4][0-9]_[0-9]{2}\s*[A-Z]?$/'
            ],
            'new-regex' => '/^[ABCDEF]{1}[1-3]{1}[0-9]{1}[A-Z]{1}([0-9]*)([_]{1}[0-9]{1,8})*$/'
        ];

        if (strlen($value) < 5)
            $this->context->buildViolation($constraint->tooShortMessage)
                ->setParameter('%string%', $value)
                ->setTranslationDomain('validators')
                ->addViolation();

        if (substr($value, 0, 2) == '**')
            return true;

        $validationFlag = true;

        $c1 = $value[0];
        $c23 = substr($value, 1, 2);
        $c4 = $value[3];
        $VL = $versionLimits;

        if (($c1 < $VL['<c1']) || ($c1 > $VL['>c1']) || ($c23 < $VL['<c23']) || ($c23 > $VL['>c23']) || ($c4 != $VL['!=c4']))
            $validationFlag = true;

        if ($validationFlag) {
            foreach ($VL['regex'] as $reg)
                if (preg_match($reg, $value))
                    return true;
        }

        if (preg_match($VL['new-regex'], $value))
            return true;

        if (!preg_match('/^[a-zA-Z0-9]+$/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}