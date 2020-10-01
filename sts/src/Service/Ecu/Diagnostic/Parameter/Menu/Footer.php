<?php

namespace App\Service\Ecu\Diagnostic\Parameter\Menu;

use App\Enum\Menu;
use App\Service\Ecu\Sw\Menu\Horizontal;

/**
 * Class Footer
 *
 * @package App\Service\Ecu\Diagnostic\Parameter\Menu
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
            'isEditAvailable' => $this->isEditAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
        ];
    }

    public function isEditAvailable(): bool
    {
        return $this->arguments['mode'] != Menu::MODE_EDIT;
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