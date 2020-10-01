<?php


namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class SameParameterNameForGlobal extends Constraint
{
    public $message = "constraints.parameterNameGlobal.message";
}