<?php

namespace App\Validator\Constraints\Parameter\CoC;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DoubleDecimalPoint extends Constraint
{
    public $message = "constraints.parameter.minMaxPropertyValue.messageComma";
}