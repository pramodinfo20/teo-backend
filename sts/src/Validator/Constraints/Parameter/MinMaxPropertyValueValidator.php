<?php

namespace App\Validator\Constraints\Parameter;

use App\Entity\GlobalParameters;
use App\Enum\Entity\VariableTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MinMaxPropertyValueValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof MinMaxPropertyValue) {
            throw new UnexpectedTypeException($constraint, MinMaxPropertyValue::class);
        }

        if (!($parameter instanceof GlobalParameters)) {
            throw new UnexpectedValueException($parameter, GlobalParameters::class);
        }

        $valueMin = $parameter->getMinValue();
        $valueMax = $parameter->getMaxValue();

        switch ($parameter->getVariableType()->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                if ($valueMin < 0)
                    $this->buildValidationMessage('minValue', $constraint->messageGrEq);
                if ($valueMax < 0)
                    $this->buildValidationMessage('maxValue', $constraint->messageGrEq);

            case VariableTypes::VARIABLE_TYPE_SIGNED:
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                if (preg_match('/.*[,.].*/', $valueMin))
                    $this->buildValidationMessage('minValue', $constraint->messageIntegers);
                if (preg_match('/.*[,.].*/', $valueMax))
                    $this->buildValidationMessage('maxValue', $constraint->messageIntegers);
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