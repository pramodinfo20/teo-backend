<?php

namespace App\Validator\Constraints\Parameter\CoC;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IntegerValue extends Constraint
{
    public $messageGrEq = "constraints.parameter.minMaxPropertyValue.messageGrEq";
}