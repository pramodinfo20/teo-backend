<?php

namespace App\Validator\Constraints\SubConfiguration\ShortKey;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TypeYearSeriesShortKey extends Constraint
{
    public $message = "constraints.subconfiguration.shortKey.typeYearSeriesShortKey.message";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}