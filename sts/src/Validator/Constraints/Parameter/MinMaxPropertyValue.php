<?php

namespace App\Validator\Constraints\Parameter;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MinMaxPropertyValue extends Constraint
{
    public $messageGrEq = "constraints.parameter.minMaxPropertyValue.messageGrEq";
    public $messageIntegers = "constraints.parameter.minMaxPropertyValue.messageIntegers";
}