<?php

namespace App\Validator\Constraints\Parameter\CoC\Management;

use App\Entity\CocParameters;
use App\Enum\Entity\VariableTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FieldPropertyValueValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof FieldPropertyValue) {
            throw new UnexpectedTypeException($constraint, FieldPropertyValue::class);
        }

        if (!($parameter instanceof CocParameters)) {
            throw new UnexpectedValueException($parameter, CocParameters::class);
        }

        $fieldValue = $parameter->getField();

        switch ($parameter->getVariableType()->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                if ($fieldValue < 0)
                    $this->buildValidationMessage('field', $constraint->messageGrEq);
            case VariableTypes::VARIABLE_TYPE_SIGNED:
            case VariableTypes::VARIABLE_TYPE_INTEGER:
            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                if (preg_match('/.*[,.].*/', $fieldValue))
                    $this->buildValidationMessage('field', $constraint->messageIntegers);
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