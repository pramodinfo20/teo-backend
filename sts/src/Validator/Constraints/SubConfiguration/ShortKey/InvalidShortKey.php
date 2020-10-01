<?php


namespace App\Validator\Constraints\SubConfiguration\ShortKey;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class InvalidShortKey extends Constraint
{
    public $message = "constraints.subconfiguration.longKey_shortKey.longKey_shortKey.message";
    public $message2 = "constraints.subconfiguration.longKey_shortKey.longKey_shortKey.message2";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}