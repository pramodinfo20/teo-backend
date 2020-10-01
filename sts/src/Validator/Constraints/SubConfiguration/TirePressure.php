<?php


namespace App\Validator\Constraints\SubConfiguration;

use Symfony\Component\Validator\Constraint;


class TirePressure extends Constraint
{
    public $message;

    /**
     * TirePressure constructor.
     * @param string $isFront
     */
    public function __construct(string $isFront)
    {
        parent::__construct();

        if ($isFront == true)
            $this->message = "constraints.subconfiguration.tirePressure.messageFront";
        else
            $this->message = "constraints.subconfiguration.tirePressure.messageRear";

    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}