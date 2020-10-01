<?php

namespace App\Validator\Constraints\Parameter\CoC\Management;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FieldPropertyValue extends Constraint
{
    public $messageGrEq = "constraints.parameter.minMaxPropertyValue.messageGrEq";
    public $messageIntegers = "constraints.parameter.minMaxPropertyValue.messageIntegers";
}