<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SameParameterName extends Constraint
{
    public $message = "constraints.parameterName.message";
}
