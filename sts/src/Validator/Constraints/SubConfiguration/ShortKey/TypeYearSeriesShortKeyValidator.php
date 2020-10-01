<?php

namespace App\Validator\Constraints\SubConfiguration\ShortKey;

use App\Model\ShortKeyModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;


class TypeYearSeriesShortKeyValidator extends ConstraintValidator
{
    public function validate($subConfiguration, Constraint $constraint): void
    {
        if (!$constraint instanceof TypeYearSeriesShortKey) {
            throw new UnexpectedTypeException($constraint, TypeYearSeriesShortKey::class);
        }

        if (!($subConfiguration instanceof ShortKeyModel)) {
            throw new UnexpectedValueException($subConfiguration, ShortKeyModel::class);
        }

        $type = strtoupper($subConfiguration->getType());
        $year = $subConfiguration->getYear();
        $series = $subConfiguration->getSeries();

        if ($type == 'D') {
            if ($year == 17) {
                if (!in_array($series, ['00', '01'])) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('%string%', "$type/$year/$series")
                        ->setTranslationDomain('validators')
                        ->addViolation();
                }
            } elseif ($year > 17) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', "$type/$year/$series")
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        } elseif ($type == 'E') {
            if (!($year >= 17 && $year <= 19)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', "$type/$year/$series")
                    ->setTranslationDomain('validators')
                    ->addViolation();
            }
        } elseif ($type > 'E') {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', "$type/$year/$series")
                ->setTranslationDomain('validators')
                ->addViolation();
        }
    }
}