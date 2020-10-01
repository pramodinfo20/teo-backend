<?php

namespace App\Validator\Constraints\EcuSwProperties;

use App\Entity\EcuSwProperties;
use App\Model\EcuSwProperties\EcuSwPropertiesCollection;
use App\Service\Ecu\Sw\Property;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistingPropertyNameValueValidator extends ConstraintValidator
{

    private $manager;

    private $property;

    /**
     * ExistingPropertyNameValueValidator constructor.
     * @param ObjectManager $manager
     * @param Property $property
     */
    public function __construct(ObjectManager $manager, Property $property)
    {
        $this->manager = $manager;
        $this->property = $property;
    }

    public function validate($collection, Constraint $constraint): void
    {
        if (!$constraint instanceof ExistingPropertyNameValue) {
            throw new UnexpectedTypeException($constraint, ExistingPropertyNameValue::class);
        }

        if (!($collection instanceof EcuSwPropertiesCollection)) {
            throw new UnexpectedValueException($collection, EcuSwPropertiesCollection::class);
        }

        $properties = $collection->getProperties();
        $names = [];
        foreach ($properties as $property) {
            if (!in_array($property->getName(), $names)) {
                array_push($names, $property->getName());
            } else {
                $this->executeViolation($constraint->msgPropExistCurrent);
            }
        }

        $propertiesFromDb = $this->manager->getRepository(EcuSwProperties::class)->findAll();

        foreach ($propertiesFromDb as $propFromDb) {
            foreach ($properties as $property) {
                if ($propFromDb->getName() == $property->getName() and
                    $propFromDb->getValue() == $property->getValue() and
                    $property->getId() == null) {
                    $this->executeViolation($constraint->msgPropExistInDb);
                }
            }
        }
    }

    private function executeViolation($message)
    {
        $this->context->buildViolation($message)
            ->setTranslationDomain('validators')
            ->addViolation();
    }
}