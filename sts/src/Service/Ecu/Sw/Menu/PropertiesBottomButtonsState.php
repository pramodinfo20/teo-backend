<?php


namespace App\Service\Ecu\Sw\Menu;


use App\Entity\EcuSwVersions;
use App\Enum\Menu;

class PropertiesBottomButtonsState extends Horizontal
{

    /**
     * @return array
     */
    public function getMenu(): array
    {
        return [
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
            'isCopyToOtherVersionAvailable' => $this->isCopyToOtherVersionAvailable(),
            'isChangeOrderAvailable' => $this->isChangeOrderAvailable(),
            'isConfirmCopyAvailable' => $this->isConfirmCopyAvailable(),
            'isChangeCanAvailable' => $this->isChangeCanAvailable(),
        ];
    }

    public function isSaveAvailable(): bool
    {
        return
            $this->arguments['mode'] == Menu::MODE_EDIT ||
            $this->arguments['mode'] == Menu::MODE_CHANGE_ORDER ||
            $this->arguments['mode'] == Menu::MODE_ADD_NEW_PROPERTY;
    }

    public function isCancelAvailable(): bool
    {
        return
            $this->arguments['mode'] == Menu::MODE_EDIT ||
            $this->arguments['mode'] == Menu::MODE_CHANGE_ORDER ||
            $this->arguments['mode'] == Menu::MODE_ADD_NEW_PROPERTY ||
            $this->arguments['mode'] == Menu::MODE_COPY_TO_ANOTHER;
    }

    public function isCopyToOtherVersionAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_VIEW;
    }

    public function isChangeOrderAvailable(): bool
    {
        return
            $this->arguments['mode'] == Menu::MODE_VIEW &&
            $this->arguments['status'] == 'in development';
    }

    public function isConfirmCopyAvailable(): bool
    {
        return $this->arguments['mode'] == Menu::MODE_COPY_TO_ANOTHER;
    }

    public function isChangeCanAvailable(): bool
    {
        return
            $this->arguments['mode'] == Menu::MODE_VIEW &&
            $this->arguments['status'] == 'in development';
    }

    /**
     * @return mixed
     */
    public function build()
    {
        $this->arguments['status'] = (!is_null($this->arguments['sw'])) ?
            $this->manager->getRepository(EcuSwVersions::class)
                ->findOneBy(['ecuSwVersionId' => $this->arguments['sw']])
                ->getReleaseStatus()->getReleaseStatusName() : 'error';
        return $this;
    }
}