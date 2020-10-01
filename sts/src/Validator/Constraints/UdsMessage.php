<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UdsMessage extends Constraint
{
    public $message = "constraints.udsMessage.message";
    public $startBitMessage = "constraints.udsMessage.startBitMessage";
    public $stopBitMessage = "constraints.udsMessage.stopBitMessage";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}