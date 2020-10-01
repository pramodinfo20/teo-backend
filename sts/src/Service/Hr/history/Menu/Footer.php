<?php

namespace App\Service\Hr\history\Menu;


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
            'isViewAvailable' => $this->isViewAvailable(),
            'isGoBackAvailable' => $this->isGoBackAvailable(),
            'isShowTreeAvailable' => $this->isShowTreeAvailable(),
        ];
    }

    public function isViewAvailable(): bool
    {
        return true;
    }

    //todo: Implement graph
    public function isGoBackAvailable(): bool
    {
        return false;
    }

    //todo: Implement graph
    public function isShowTreeAvailable(): bool
    {
        return true;
    }
}