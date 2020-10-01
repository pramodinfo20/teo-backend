<?php

namespace App\Validator\Constraints\SubConfiguration;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DeprecatedConfiguration extends Constraint
{
    public $message = "Configuration deprecated!";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}