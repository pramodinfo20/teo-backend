<?php

namespace App\Validator\Constraints\SubConfiguration\LongKey;

use App\Model\LongKeyModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;


class TypeYearSeriesLongKeyValidator extends ConstraintValidator
{
    public function validate($subConfiguration, Constraint $constraint): void
    {
        if (!$constraint instanceof TypeYearSeriesLongKey) {
            throw new UnexpectedTypeException($constraint, TypeYearSeriesLongKey::class);
        }

        if (!($subConfiguration instanceof LongKeyModel)) {
            throw new UnexpectedValueException($subConfiguration, LongKeyModel::class);
        }

        $type = strtoupper($subConfiguration->getType());
        $year = $subConfiguration->getYear();
        $series = $subConfiguration->getSeries();

        // LONG_KEY for configuration all $type ($type = B, $type = D and $type = E)
        if ($type == 'D') {
            if ($year == 17) {
                if (in_array($series, ['00', '01'])) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('%string%', "$type/$year/$series")
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }
            }
        } 
        // SHORT_KEY for configuration $type < D 
        /*elseif ($type < 'D') {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', "$type/$year/$series")
                ->setTranslationDomain('validators')
                ->addViolation();
        }*/ 
        // SHORT_KEY for configuration $type == E
        /*elseif ($type == 'E') {
            if ($year >= 17 && $year <= 19) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', "$type/$year/$series")
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        }*/
    }
}