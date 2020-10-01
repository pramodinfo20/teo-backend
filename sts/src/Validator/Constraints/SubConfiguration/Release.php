<?php

namespace App\Validator\Constraints\SubConfiguration;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class Release extends Constraint
{
    public $assignedSoftware = "constraints.subconfiguration.release.assignedSoftwareMessage";
    public $releasedSoftware = "constraints.subconfiguration.release.releasedSoftwareMessage";
    public $assignedGlobalsWithValues = "constraints.subconfiguration.release.assignedGlobalsWithValuesMessage";


    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}