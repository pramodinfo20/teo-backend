<?php


namespace App\Service\Vehicles\Configuration;

use App\Entity\SubVehicleConfigurations;
use Doctrine\Common\Persistence\ObjectManager;


abstract class VehicleConfigurationBase
{
    protected $manager;
    protected $subVehConfId;
    protected $subConfiguration;

    public function __construct(ObjectManager $manager, SubVehicleConfigurations $subConfiguration)
    {
        $this->manager = $manager;
        $this->subConfiguration = $subConfiguration;
        $this->subVehConfId = $subConfiguration->getSubVehicleConfigurationId();
    }

    public abstract function getParametersToView();

    public abstract function getModelToEdit();
}
