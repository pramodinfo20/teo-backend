<?php

namespace App\Service\UserRole\Menu;


use App\Enum\Menu;
use App\Service\Ecu\Sw\Menu\Horizontal;

/**
 * Class Footer
 *
 * @package App\Service\Hr\history\menu
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
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isGoBackAvailable' => $this->isGoBackAvailable(),
            'isShowTreeAvailable' => $this->isShowTreeAvailable(),
        ];
    }

    public function isSaveAvailable(): bool
    {
        return true;
    }

    public function isGoBackAvailable() : bool
    {
        return true;
    }

    public function isShowTreeAvailable(): bool
    {
        return true;
    }
}