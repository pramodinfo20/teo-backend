<?php

namespace App\Validator\Constraints\Parameter;

use App\Entity\GlobalParameters;
use App\Enum\Entity\VariableTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MinMaxPropertyNotEmptyValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof MinMaxPropertyNotEmpty) {
            throw new UnexpectedTypeException($constraint, MinMaxPropertyNotEmpty::class);
        }

        if (!($parameter instanceof GlobalParameters)) {
            throw new UnexpectedValueException($parameter, GlobalParameters::class);
        }

        switch ($parameter->getVariableType()->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
            case VariableTypes::VARIABLE_TYPE_SIGNED:
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:

                if (is_null($parameter->getMinValue()))
                    $this->buildValidationMessage('minValue', $constraint->message);

                if (is_null($parameter->getMaxValue() ))
                    $this->buildValidationMessage('maxValue', $constraint->message);

                break;
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