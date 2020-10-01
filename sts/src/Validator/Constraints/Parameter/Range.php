<?php

namespace App\Validator\Constraints\Parameter;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Range extends Constraint
{
    public $message = "constraints.parameter.range.message";
    public $message2 = "constraints.parameter.range.message2";
}