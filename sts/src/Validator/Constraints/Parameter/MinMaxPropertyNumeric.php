<?php

namespace App\Validator\Constraints\Parameter;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MinMaxPropertyNumeric extends Constraint
{
    public $message = "constraints.parameter.minMaxPropertyNumeric.message";
}