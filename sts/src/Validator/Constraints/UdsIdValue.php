<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UdsIdValue extends Constraint
{
    public $message = "constraints.udsIdValue.message";
    public $message2 = "constraints.udsIdValue.message2";


    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}