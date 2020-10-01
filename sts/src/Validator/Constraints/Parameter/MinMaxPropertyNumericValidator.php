<?php

namespace App\Validator\Constraints\Parameter;

use App\Entity\GlobalParameters;
use App\Enum\Entity\VariableTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MinMaxPropertyNumericValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof MinMaxPropertyNumeric) {
            throw new UnexpectedTypeException($constraint, MinMaxPropertyNumeric::class);
        }

        if (!($parameter instanceof GlobalParameters)) {
            throw new UnexpectedValueException($parameter, GlobalParameters::class);
        }

        switch ($parameter->getVariableType()->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
            case VariableTypes::VARIABLE_TYPE_SIGNED:
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                if (!is_numeric($parameter->getMinValue()))
                    $this->buildValidationMessage('minValue', $constraint->message);

                if (!is_numeric($parameter->getMaxValue()))
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