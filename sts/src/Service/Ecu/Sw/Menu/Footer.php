<?php

namespace App\Service\Ecu\Sw\Menu;

use App\Entity\EcuSwVersions;
use App\Enum\Menu;

/**
 * Class Footer
 *
 * @package App\Service\Ecu\Sw\Menu
 */
class Footer extends Horizontal
{
    public function build(): self
    {
        $this->arguments['status'] = (!is_null($this->arguments['sw'])) ?
            $this->manager->getRepository(EcuSwVersions::class)
                ->findOneBy(['ecuSwVersionId' => $this->arguments['sw']])
                ->getReleaseStatus()->getReleaseStatusName() : 'error';
        return $this;
    }

    public function getMenu(): array
    {
        return [
            'isEditAvailable' => $this->isEditAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
            'isCopyAvailable' => $this->isCopyAvailable(),
            'isCopyCurrentAvailable' => $this->isCopyCurrentAvailable(),
            'isCopyOtherAvailable' => $this->isCopyOtherAvailable(),
            'isGenerateOdxAvailable' => $this->isGenerateOdxAvailable(),
            'isDeleteAvailable' => $this->isDeleteAvailable(),
            'isChangeOrderAvailable' => $this->isChangeOrderAvailable(),
        ];
    }

    public function isEditAvailable(): bool
    {
        return $this->arguments['mode'] !== Menu::MODE_EDIT && $this->arguments['copy'] != 1
            && $this->arguments['add'] != 1 && $this->arguments['delete'] != 1
            && $this->arguments['order'] != 1 && $this->arguments['status'] == 'in development';
    }

    public function isSaveAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT || $this->arguments['copy'] == 1
            || $this->arguments['order'] == 1 || $this->arguments['add'] == 1
            || $this->arguments['delete'] == 1;
    }

    public function isCancelAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_EDIT || $this->arguments['copy'] == 1
            || $this->arguments['add'] == 1 || $this->arguments['delete'] == 1
            || $this->arguments['order'] == 1;
    }

    public function isCopyAvailable(): bool
    {
        return $this->arguments['mode'] != Menu::MODE_EDIT && $this->arguments['add'] != 1
            && $this->arguments['delete'] != 1 && $this->arguments['order'] != 1;
    }

    public function isCopyCurrentAvailable(): bool
    {
        return $this->arguments['copy'] == 1 && $this->arguments['status'] == 'in development';
    }

    public function isCopyOtherAvailable(): bool
    {
        return $this->arguments['copy'] == 1;
    }

    public function isGenerateOdxAvailable(): bool
    {
        return $this->arguments['mode'] !== Menu::MODE_EDIT && $this->arguments['add'] != 1
            && $this->arguments['delete'] != 1 && $this->arguments['order'] != 1
            && $this->arguments['copy'] != 1;;
    }

    public function isDeleteAvailable(): bool
    {
        return $this->arguments['mode'] != Menu::MODE_EDIT && $this->arguments['add'] != 1
            && $this->arguments['delete'] != 1 && $this->arguments['order'] != 1
            && $this->arguments['copy'] != 1 && $this->arguments['status'] == 'in development';
    }

    public function isChangeOrderAvailable(): bool
    {
        return $this->arguments['mode'] != Menu::MODE_EDIT && $this->arguments['add'] != 1
            && $this->arguments['delete'] != 1 && $this->arguments['order'] != 1
            && $this->arguments['copy'] != 1 && $this->arguments['status'] == 'in development';
    }
}