<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StsVersion extends Constraint
{
    public $tooShortMessage = "constraints.stsVersion.tooShortMessage";
    public $message = "constraints.stsVersion.message";
}