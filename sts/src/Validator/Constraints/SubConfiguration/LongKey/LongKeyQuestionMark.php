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
class LongKeyQuestionMark extends Constraint
{
    public $message = "constraints.subconfiguration.longKey_shortKey.longKeyQuestionMark_shortKeyQuestionMark.message";
    public $message2 = "constraints.subconfiguration.longKey_shortKey.longKeyQuestionMark_shortKeyQuestionMark.message2";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}