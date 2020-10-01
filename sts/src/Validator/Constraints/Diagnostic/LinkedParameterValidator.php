<?php
namespace App\Validator\Constraints\Diagnostic;

use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuSwParameters;
use App\Model\Diagnostic\DynamicParametersCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LinkedParameterValidator extends ConstraintValidator
{
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function validate($collection, Constraint $constraint): void
    {
        if (!$constraint instanceof LinkedParameter) {
            throw new UnexpectedTypeException($constraint, LinkedParameter::class);
        }

        if (!($collection instanceof DynamicParametersCollection)) {
            throw new UnexpectedValueException($collection, DynamicParametersCollection::class);
        }

        $parameters = $collection->getParameters();

        $toRemove = [];
        $allParameters = $this->manager->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)->findAll();
        foreach ($allParameters as &$parameter) {
            foreach ($parameters as $leftParameter)  {
                if ($parameter->getDpvbdsId() == $leftParameter->getParameterId()) {
                    $toRemove[] = $parameter;
                }
            }
        }

       $allParameters = array_diff($allParameters, $toRemove);

        foreach ($allParameters as $parameter) {
            $linkedParameters = $this->manager->getRepository(EcuSwParameters::class)
                ->findBy(['dynamicParameterValuesByDiagnosticSoftware' => $parameter->getDpvbdsId()]);

            if (!empty($linkedParameters)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%parameterValue%', $parameter->getDynamicParameterValuesByDiagnosticSoftwareName())
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }
    }
}