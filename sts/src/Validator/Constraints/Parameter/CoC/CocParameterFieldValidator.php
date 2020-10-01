<?php

namespace App\Validator\Constraints\Parameter\CoC;

use App\Enum\Entity\VariableTypes;
use App\Model\Parameter\CocParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CocParameterFieldValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof CocParameterField) {
            throw new UnexpectedTypeException($constraint, CocParameterField::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (preg_match('/([^a-zA-Z0-9\.])/', $value)) {
            $this->buildValidationMessage('valueField', $constraint->message);
        }

        $arrayFromVal = str_split($value);
        $dotCounter = 0;
        $stringAsNumber = '';

        for ($i = 0; $i < strlen($value); $i++) {
            if (is_numeric($arrayFromVal[$i])) {
                $stringAsNumber = $stringAsNumber . $arrayFromVal[$i];
            }
            elseif ($arrayFromVal[$i] == '.') {
                $stringAsNumber = '';
                $dotCounter++;
            }

            if (strlen($stringAsNumber) == 3 && intval($stringAsNumber) >= 100) {
                $this->buildValidationMessage('valueField', $constraint->message);
                break;
            }

            if ($dotCounter > 2) {
                $this->buildValidationMessage('valueField', $constraint->message);
                break;
            }
        }
    }

    /**
     * @param $propertyName
     * @param $message
     */
    private function buildValidationMessage($propertyName, $message)
    {
        $this->context
            ->buildViolation($message)
            ->atPath($propertyName)
            ->setTranslationDomain('validators')
            ->addViolation();
    }
}