<?php

namespace App\Model\History;

class HistoryType
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var bool
     */
    private $ecuParameterManagement;

    /**
     * @var bool
     */
    private $vehicleConfigurationSvcManagement;

    /**
     * @var bool
     */
    private $vehicleConfigurationVcManagement;

    /**
     * @var bool
     */
    private $cocValuesSetsAssignment;

    /**
     * @var bool
     */
    private $softwareManagement;

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return HistoryType
     */
    public function setType(int $type = null): HistoryType
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEcuParameterManagement(): ?bool
    {
        return $this->ecuParameterManagement;
    }

    /**
     * @param bool $ecuParameterManagement
     *
     * @return HistoryType
     */
    public function setEcuParameterManagement(bool $ecuParameterManagement = null): HistoryType
    {
        $this->ecuParameterManagement = $ecuParameterManagement;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVehicleConfigurationSvcManagement(): ?bool
    {
        return $this->vehicleConfigurationSvcManagement;
    }

    /**
     * @param bool $vehicleConfigurationSvcManagement
     *
     * @return HistoryType
     */
    public function setVehicleConfigurationSvcManagement(bool $vehicleConfigurationSvcManagement = null): HistoryType
    {
        $this->vehicleConfigurationSvcManagement = $vehicleConfigurationSvcManagement;
        return $this;
    }

    /**
     * @return bool
     */
    public function isVehicleConfigurationVcManagement(): ?bool
    {
        return $this->vehicleConfigurationVcManagement;
    }

    /**
     * @param bool $vehicleConfigurationVcManagement
     *
     * @return HistoryType
     */
    public function setVehicleConfigurationVcManagement(bool $vehicleConfigurationVcManagement = null): HistoryType
    {
        $this->vehicleConfigurationVcManagement = $vehicleConfigurationVcManagement;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCocValuesSetsAssignment(): bool
    {
        return $this->cocValuesSetsAssignment;
    }

    /**
     * @param bool $cocValuesSetsAssignment
     *
     * @return HistoryType
     */
    public function setCocValuesSetsAssignment(bool $cocValuesSetsAssignment): HistoryType
    {
        $this->cocValuesSetsAssignment = $cocValuesSetsAssignment;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSoftwareManagement(): bool
    {
        return $this->softwareManagement;
    }

    /**
     * @param bool $softwareManagement
     *
     * @return HistoryType
     */
    public function setSoftwareManagement(bool $softwareManagement): HistoryType
    {
        $this->softwareManagement = $softwareManagement;
        return $this;
    }
}