<?php
namespace App\Validator\Constraints\EcuSwProperties;

use Symfony\Component\Validator\Constraint;

class ExistingPropertyNameValue extends Constraint
{
    public $msgPropExistCurrent = "constraints.ecuSwProperties.ExistingPropertyName.msgPropExistCurrent";
    public $msgPropExistInDb = "constraints.ecuSwProperties.ExistingPropertyName.msgPropExistInDb";
}
