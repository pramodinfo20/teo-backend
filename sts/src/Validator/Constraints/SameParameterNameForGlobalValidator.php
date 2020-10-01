<?php


namespace App\Validator\Constraints;


use App\Enum\Parameter;
use App\Model\Odx2Collection;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SameParameterNameForGlobalValidator extends ConstraintValidator
{
    public function validate($collection, Constraint $constraint): void
    {
        if (!$constraint instanceof SameParameterNameForGlobal) {
            throw new UnexpectedTypeException($constraint, SameParameterNameForGlobal::class);
        }

        $parameters = $collection->getParameters();
        $pairs = [];
        foreach ($parameters as $parameter) {
            if ($parameter->getLinkingType() == Parameter::LINKING_TYPE_GLOBAL_PARAMETER) {
                $tmp = [$parameter->getName(), $parameter->getLinkedToGlobalParameter()->getGlobalParameterId()];
                if (!in_array($tmp, $pairs)) {
                    array_push($pairs, $tmp);
                } else {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('%parameterName%', $parameter->getName())
                        ->setParameter('%globalName%', $parameter->getLinkedToGlobalParameter()
                            ->getGlobalParameterName())
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }
            }
        }
    }
}