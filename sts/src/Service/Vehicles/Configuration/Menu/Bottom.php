<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/27/19
 * Time: 2:37 PM
 */

namespace App\Service\Vehicles\Configuration\Menu;

use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Service\Ecu\Sw\Menu\Horizontal;
use Doctrine\Common\Persistence\ObjectManager;

class Bottom extends Horizontal
{
    const PREVIEW_MODE = 0;
    const EDIT_MODE = 1;
    const CREATE_MODE = 2;
    const CREATE_COPY_MODE = 3;
    const FIX_MODE = 4;
    const START_MODE = 5;

    public function getMenu(): array
    {
        return [
            'isEditAvailable' => $this->isEditAvailable(),
            'isFixAvailable' => $this->isFixAvailable(),
            'isSaveAvailable' => $this->isSaveAvailable(),
            'isCancelAvailable' => $this->isCancelAvailable(),
            'isCreateAvailable' => $this->isCreateAvailable(),
            'isCreateByCopyAvailable' => $this->isCreateByCopyAvailable(),
            'isDeleteAvailable' => $this->isDeleteAvailable(),
            'isMainView' => $this->isMainView(),
            'isHistoryAvailable' => $this->isHistoryAvailable()
        ];
    }

    private function isEditAvailable(): bool
    {
        $numberOfCars = (!is_null($this->arguments['configuration']) ? $this->manager->getRepository(VehicleConfigurations::class)
            ->getNumberOfCars($this->arguments['configuration']->getVehicleConfigurationId())['number'] : 0);

        if (!is_null($this->arguments['subConfiguration']) || !is_null($this->arguments['configuration'])) {
            if (!is_null($this->arguments['subConfiguration'])) {
                $type = strtoupper($this->arguments['subConfiguration']->getVehicleConfiguration()->getVehicleTypeName());
                $year = $this->arguments['subConfiguration']->getVehicleConfiguration()->getVehicleTypeYear();
                $series = (int)$this->arguments['subConfiguration']->getVehicleConfiguration()->getVehicleSeries();
            } else {
                $type = strtoupper($this->arguments['configuration']->getVehicleTypeName());
                $year = $this->arguments['configuration']->getVehicleTypeYear();
                $series = (int)$this->arguments['configuration']->getVehicleSeries();
            }


            if ($type == 'D' or $type == 'B') {
                if ($year < 16) {
                    return false;

                } elseif ($year == 16) {
                    if ($series < 3) {
                        return false;
                    }
                }
            }
        }

        return ($this->arguments['mode'] == self::PREVIEW_MODE &&
            ((!is_null($this->arguments['subConfiguration']) && !$this->arguments['subConfiguration']->getVehicleConfiguration()->getDraft()) ||
                (!is_null($this->arguments['configuration']) && !$this->arguments['configuration']->getDraft() &&
                    (is_null($numberOfCars) || $numberOfCars == 0)))
        );
    }

    private function isFixAvailable(): bool
    {
        return ($this->arguments['mode'] == self::PREVIEW_MODE &&
            ((!is_null($this->arguments['subConfiguration']) && $this->arguments['subConfiguration']->getVehicleConfiguration()->getDraft()) ||
                (!is_null($this->arguments['configuration']) && $this->arguments['configuration']->getDraft()))
        );
    }

    private function isSaveAvailable(): bool
    {
        return (in_array($this->arguments['mode'], [self::CREATE_COPY_MODE, self::CREATE_MODE, self::EDIT_MODE, self::FIX_MODE]));
    }

    private function isCancelAvailable(): bool
    {
        return (in_array($this->arguments['mode'], [self::CREATE_COPY_MODE, self::CREATE_MODE, self::EDIT_MODE, self::FIX_MODE]));
    }

    private function isCreateAvailable(): bool
    {
        return ($this->arguments['mode'] == self::PREVIEW_MODE || $this->arguments['mode'] == self::START_MODE) && is_null($this->arguments['configuration']);
    }

    private function isCreateByCopyAvailable(): bool
    {
        return ($this->arguments['mode'] == self::PREVIEW_MODE && !is_null($this->arguments['subConfiguration'])) && is_null($this->arguments['configuration']);
    }

    private function isDeleteAvailable(): bool
    {
        $numberOfCarsConfiguration = (!is_null($this->arguments['configuration']) ? $this->manager->getRepository
        (VehicleConfigurations::class)
            ->getNumberOfCars($this->arguments['configuration']->getVehicleConfigurationId())['number'] : 0);

        $numberOfCarsSubConfiguration = (!is_null($this->arguments['subConfiguration']) ? $this->manager->getRepository
        (SubVehicleConfigurations::class)
            ->getNumberOfCars($this->arguments['subConfiguration']->getSubVehicleConfigurationId())['number'] : 0);
        return !$numberOfCarsConfiguration &&  !$numberOfCarsSubConfiguration && (!is_null($this->arguments['configuration']) ||
                !is_null($this->arguments['subConfiguration']));
    }

    private function isMainView(): bool
    {
        return ($this->arguments['mode'] == self::START_MODE);
    }

    private function isHistoryAvailable() : bool
    {
        return ($this->arguments['mode'] != self::START_MODE && $this->arguments['mode'] != self::CREATE_MODE);
    }

    /**
     * @return mixed
     */
    public function build()
    {
        return $this;
    }
}