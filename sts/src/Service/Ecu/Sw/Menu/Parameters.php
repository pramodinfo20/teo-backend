<?php

namespace App\Service\Ecu\Sw\Menu;

use App\Entity\EcuSwVersions;
use App\Service\Ecu\Sw\Generator;

/**
 * Class Parameter
 *
 * @package App\Service\Ecu\Sw\Menu
 */
class Parameters extends Horizontal
{
    public function build(): self
    {
        if ($this->arguments['ecu'] && $this->arguments['sw']) {
            $this->arguments['subversion_parent'] = $this->manager
                ->getRepository(EcuSwVersions::class)
                ->getParentIdIfExistsById($this->arguments['sw'])[0]['parent'];

            if ($this->arguments['subversion_parent'] != null) {
                $this->arguments['parent_locked'] = $this->manager
                    ->getRepository(EcuSwVersions::class)
                    ->getLockStatusById($this->arguments['subversion_parent'])[0]['status'];
            }

            $this->arguments['current_locked'] = $this->manager
                ->getRepository(EcuSwVersions::class)
                ->getLockStatusById($this->arguments['sw'])[0]['status'];

        }

        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isCreateAvailable' => $this->isCreateAvailable(),
            'isSwSubversionCreateAvailable' => $this->isSwSubversionCreateAvailable(),
            'isSwRemoveAvailable' => $this->isSwRemoveAvailable(),
            'isSwCopyAvailable' => $this->isSwCopyAvailable(),
            'isSwLocked' => $this->isSwLocked(),
            'isListExportAvailable' => $this->isListExportAvailable(),
            'isHistoryAvailable' => $this->isHistoryAvailable(),
            'isRvSAvailable' => $this->isRvSAvailable()
        ];
    }

    public function isCreateAvailable(): bool
    {
        return array_key_exists('subversion_parent', $this->arguments)
            && $this->arguments['subversion_parent'] == null;
    }

    public function isSwSubversionCreateAvailable(): bool
    {
        return array_key_exists('subversion_parent', $this->arguments)
            && $this->arguments['subversion_parent'] == null;
    }

    public function isSwRemoveAvailable(): bool
    {
        return true;
    }

    public function isSwCopyAvailable(): bool
    {
        return array_key_exists('subversion_parent', $this->arguments) && $this->arguments['subversion_parent'] == null;
    }

    public function isSwLocked(): bool
    {
        return array_key_exists('current_locked', $this->arguments) && $this->arguments['current_locked'] == 'locked';
    }

    public function isListExportAvailable(): bool
    {
        return false;
    }

    public function isHistoryAvailable(): bool
    {
        return true;
    }

    public function isRvSAvailable(): bool
    {
        return false;
    }
}