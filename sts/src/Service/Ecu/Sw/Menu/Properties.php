<?php

namespace App\Service\Ecu\Sw\Menu;

use App\Entity\EcuSwVersions;
use App\Enum\Menu;

/**
 * Class Properties
 *
 * @package App\Service\Ecu\Sw\Menu
 */
class Properties extends Horizontal
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

        $this->arguments['status'] = (!is_null($this->arguments['sw'])) ?
            $this->manager->getRepository(EcuSwVersions::class)
                ->findOneBy(['ecuSwVersionId' => $this->arguments['sw']])
                ->getReleaseStatus()->getReleaseStatusName() : 'error';

        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isCreateNewAvailable' => $this->isCreateNewAvailable(),
            'isCreateFromListAvailable' => $this->isCreateFromListAvailable(),
            'isListExportAvailable' => $this->isListExportAvailable(),
            'isHistoryAvailable' => $this->isHistoryAvailable(),
            'isRvSAvailable' => $this->isRvSAvailable(),
            'isSwLocked' => $this->isSwLocked(),
        ];
    }

    public function isCreateNewAvailable(): bool
    {
        return array_key_exists('subversion_parent', $this->arguments)
            && $this->arguments['subversion_parent'] == null
            && $this->arguments['mode'] == Menu::MODE_VIEW &&
            $this->arguments['status'] == 'in development';
    }

    public function isCreateFromListAvailable(): bool
    {
        return array_key_exists('subversion_parent', $this->arguments)
            && $this->arguments['subversion_parent'] == null
            && $this->arguments['mode'] == Menu::MODE_VIEW &&
            $this->arguments['status'] == 'in development';
    }

    public function isListExportAvailable(): bool
    {
        return false;
    }

    public function isHistoryAvailable(): bool
    {
        return false;
    }

    public function isRvSAvailable(): bool
    {
        return false;
    }

    public function isSwLocked(): bool
    {
        return array_key_exists('current_locked', $this->arguments) && $this->arguments['current_locked'] == 'locked';
    }
}