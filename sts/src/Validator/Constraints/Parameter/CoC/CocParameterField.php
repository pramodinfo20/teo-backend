<?php

namespace App\Validator\Constraints\Parameter\CoC;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CocParameterField extends Constraint
{
    public $message = "constraints.parameter.notAllowedValue.message";
}