<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Windchill extends Constraint
{
    public $message = "constraints.windchill.message";
}