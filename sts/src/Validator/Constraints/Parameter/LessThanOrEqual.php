<?php

namespace App\Validator\Constraints\Parameter;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LessThanOrEqual extends Constraint
{
    public $message = "constraints.parameter.lessThanOrEqual.message";
}