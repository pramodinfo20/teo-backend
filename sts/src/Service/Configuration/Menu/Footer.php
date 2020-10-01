<?php

namespace App\Service\Configuration\Menu;


use App\Entity\SubVehicleConfigurations;
use App\Service\Ecu\Sw\Menu\Horizontal;

/**
 * Class Footer
 *
 * @package App\Service\Configuration\Menu
 */
class Footer extends Horizontal
{

    public function build(): self
    {
        if ($this->arguments['subConfId']) {
            $this->arguments['confStatus'] = $this->manager
                ->getRepository(SubVehicleConfigurations::class)
                ->findOneBy(['subVehicleConfigurationId' => $this->arguments['subConfId']])
                ->getVehicleConfigurationState()
                ->getVehicleConfigurationStateName();

        } else
            $this->arguments['confStatus'] = 'error';

        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isEditAvailable' => $this->isEditAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
        ];
    }

    public function isEditAvailable(): bool
    {
        return (($this->arguments['confStatus'] == 'Under development') ? true : false) AND ($this->arguments['mode'] == 1) ? true : false;
    }

    public function isSaveAvailable(): bool
    {
        return ($this->arguments['mode'] == 1) ? false : true;
    }

    public function isCancelAvailable(): bool
    {
        return ($this->arguments['mode'] == 1) ? false : true;
    }
}