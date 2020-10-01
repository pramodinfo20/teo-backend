<?php
namespace App\Validator\Constraints\Diagnostic;

use Symfony\Component\Validator\Constraint;

class LinkedParameter extends Constraint
{
    public $message = "Parameter: %parameterValue% is linked to sw";
}
