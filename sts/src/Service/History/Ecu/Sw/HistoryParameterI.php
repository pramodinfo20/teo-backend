<?php

namespace App\Service\History\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Model\OdxCollection;

interface HistoryParameterI
{
    /**
     * Save a non-entity data from form with transactions
     *
     * @param OdxCollection $collection
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions $sw
     * @param array $parametersBag
     * @param int $odx
     *
     * @throws \Exception
     */
    public function save(
        OdxCollection $collection, ConfigurationEcus $ecu, EcuSwVersions $sw, array $parametersBag, int $odx
    ): void;

    /**
     * Change parameters order with array
     *
     * @param EcuSwVersions $sw
     * @param array         $orders
     *
     * @return void
     * @throws \Exception
     */
    public function changeOrders(EcuSwVersions $sw, array $orders): void;

    /**
     * Save cloned Parameters for Sw
     *
     * @param EcuSwVersions $sw
     * @param array         $form
     *
     * @return void
     * @throws \Exception
     */
    public function saveClonedParameterForSwId(EcuSwVersions $sw, array $form): void;

    /**
     * Resolve conflicts for selected parameters and sw
     *
     * @param EcuSwVersions $swDestination
     * @param array         $parametersConflict
     * @param array         $parametersWConflict
     *
     * @return string
     * @throws \Exception
     */
    public function resolveConflicts(
        EcuSwVersions $swDestination,
        array $parametersConflict,
        array $parametersWConflict
    ): string;

    /**
     * Copy parameter to other sw without conflict
     *
     * @param EcuSwVersions      $swDestination
     * @param array              $parametersWConflict
     *
     * @return string
     * @throws \Exception
     */
    public function copyWithoutConflict(EcuSwVersions $swDestination, array $parametersWConflict): string;
}