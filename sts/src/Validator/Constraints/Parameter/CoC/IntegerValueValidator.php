<?php

namespace App\Validator\Constraints\Parameter\CoC;

use App\Enum\Entity\VariableTypes;
use App\Model\Parameter\CocParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IntegerValueValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof IntegerValue) {
            throw new UnexpectedTypeException($constraint, IntegerValue::class);
        }

        if (!($parameter instanceof CocParameter)) {
            throw new UnexpectedValueException($parameter, CocParameter::class);
        }

        $value = $parameter->getValueInteger();
        $variableType = $parameter->getVariableTypeId();

        if ($variableType == VariableTypes::VARIABLE_TYPE_UNSIGNED) {
            if ($value < 0) {
                $this->buildValidationMessage('valueInteger', $constraint->messageGrEq);
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