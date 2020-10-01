<?php

namespace App\Service\Parameter\Menu\Coc;

use App\Enum\Menu;
use App\Service\Ecu\Sw\Menu\Horizontal;

/**
 * Class Footer
 *
 * @package App\Service\Parameter\Menu
 */
class Footer extends Horizontal
{
    public function build(): self
    {
        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isHistoryAvailable' => $this->isHistoryAvailable(),
            'isEditAvailable' => $this->isEditAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
        ];
    }

    public function isHistoryAvailable(): bool
    {
        return true;
    }

    public function isEditAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_VIEW && (!is_null($this->arguments['subConf']) ?
                $this->arguments['subConf']->getVehicleConfigurationState()->getVehicleConfigurationStateId() == Menu::CONFIGURATION_STATE_UNDER_DEVELOPMENT : false) and
             $this->arguments['releaseStatus'] != Menu::RELEASE_STATUS_RELEASED;
    }

    public function isSaveAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT;
    }

    public function isCancelAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT;
    }
}