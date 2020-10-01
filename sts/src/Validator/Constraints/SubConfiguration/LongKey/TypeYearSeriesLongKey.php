<?php

namespace App\Validator\Constraints\SubConfiguration\LongKey;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TypeYearSeriesLongKey extends Constraint
{
    public $message = "constraints.subconfiguration.longKey.typeYearSeriesLongKey.message";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}