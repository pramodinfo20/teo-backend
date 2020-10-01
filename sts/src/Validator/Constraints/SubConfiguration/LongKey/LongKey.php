<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/30/19
 * Time: 11:39 AM
 */

namespace App\Validator\Constraints\SubConfiguration\LongKey;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LongKey extends Constraint
{
    public $message = "constraints.subconfiguration.longKey_shortKey.longKey_shortKey.message";
    public $message2 = "constraints.subconfiguration.longKey_shortKey.longKey_shortKey.message2";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}