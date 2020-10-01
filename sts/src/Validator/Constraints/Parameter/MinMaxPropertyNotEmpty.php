<?php

namespace App\Validator\Constraints\Parameter;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MinMaxPropertyNotEmpty extends Constraint
{
    public $message = "constraints.parameter.minMaxPropertyNotEmpty.message";
}