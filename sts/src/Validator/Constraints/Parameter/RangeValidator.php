<?php

namespace App\Validator\Constraints\Parameter;

use App\Enum\Entity\VariableTypes;
use App\Model\Parameter\GlobalParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RangeValidator extends ConstraintValidator
{
    public function validate($parameter, Constraint $constraint): void
    {
        if (!$constraint instanceof Range) {
            throw new UnexpectedTypeException($constraint, Range::class);
        }

        if (!($parameter instanceof GlobalParameter)) {
            throw new UnexpectedValueException($parameter, GlobalParameter::class);
        }

        $value = null;
        $validateError = false;
        $validationMessage = null;

        switch ($parameter->getVariableTypeId()) {
            case VariableTypes::VARIABLE_TYPE_STRING:
            case VariableTypes::VARIABLE_TYPE_ASCII:
            case VariableTypes::VARIABLE_TYPE_BLOB:
            case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                break;

            case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                $value = $parameter->getValueBiginteger();
                if ($parameter->getMin() > $value || $parameter->getMax() < $value)
                    $validateError = true;
                $validationMessage = $constraint->message;
                break;
            case VariableTypes::VARIABLE_TYPE_SIGNED:
                $value = $parameter->getValueSigned();
                if ($parameter->getMin() > $value || $parameter->getMax() < $value)
                    $validateError = true;
                $validationMessage = $constraint->message;
                break;
            case VariableTypes::VARIABLE_TYPE_INTEGER:
                $value = $parameter->getValueInteger();
                if ($parameter->getMin() > $value || $parameter->getMax() < $value)
                    $validateError = true;
                $validationMessage = $constraint->message;
                break;
            case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                $value = $parameter->getValueUnsigned();
                if ($parameter->getMin() > $value || $parameter->getMax() < $value)
                    $validateError = true;
                $validationMessage = $constraint->message;
                break;
        }

        if ($validateError) {
            $this->context->buildViolation($validationMessage)
                ->setParameters([
                    '%min%' => $parameter->getMin(),
                    '%max%' => $parameter->getMax()
                ])
                ->atPath('value')
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}