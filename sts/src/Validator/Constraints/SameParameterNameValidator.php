<?php
namespace App\Validator\Constraints;

use App\Model\Odx2Collection;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SameParameterNameValidator extends ConstraintValidator
{
    public function validate($collection, Constraint $constraint): void
    {
        if (!$constraint instanceof SameParameterName) {
            throw new UnexpectedTypeException($constraint, SameParameterName::class);
        }

        if (!($collection instanceof Odx2Collection)) {
            throw new UnexpectedValueException($collection, Odx2Collection::class);
        }

        $parameters = $collection->getParameters();
        $names = [];
        foreach ($parameters as $parameter) {
            if (!in_array($parameter->getName(), $names)) {
                array_push($names, $parameter->getName());
            } else {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%parameterName%', $parameter->getName())
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}