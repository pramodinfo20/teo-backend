<?php

namespace App\Service\Parameter\Menu;


use App\Enum\Menu;
use App\Service\Ecu\Sw\Menu\Horizontal;

/**
 * Class Footer
 *
 * @package App\Service\Parameter\Menu
 */
class Footer extends Horizontal
{
    //todo: block copy and edit in released subconf
    public function build(): self
    {
        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isEditAvailable' => $this->isEditAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
            'isCopyAvailable' => $this->isCopyAvailable(),
        ];
    }

    public function isEditAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_VIEW && (!is_null($this->arguments['subConf']) ?
                $this->arguments['subConf']->getVehicleConfigurationState()->getVehicleCOnfigurationStateId() == Menu::CONFIGURATION_STATE_UNDER_DEVELOPMENT : false);
    }

    public function isSaveAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT;
    }

    public function isCancelAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT;
    }

    public function isCopyAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_VIEW && (!is_null($this->arguments['globalParameters']) ?
                !is_null($this->arguments['globalParameters']->getGlobalParameterValueSetId()) : false);
    }
}