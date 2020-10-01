<?php

namespace App\Model\History;

use App\Model\History\Traits\HistoryEvent;

class HistoryConfiguration implements HistoryI
{
    use HistoryEvent;

    /**
     * @var int
     */
    private $keyType;

    /**
     * @var HistoryConfigurationI
     */
    private $historyConfiguration;

    /**
     * @var HistoryConfigurationMenu
     */
    private $historyConfigurationMenu;

    /**
     * @var array
     */
    private $keyOptions;

    /**
     * @var array
     */
    private $stsProductionLocations;

    /**
     * @var array
     */
    private $configurationColors;

    /**
     * @var array
     */
    private $releaseStates;

    /**
     * @var array
     */
    private $users;

    /**
     * @var array
     */
    private $ecus;

    /**
     * @return int
     */
    public function getKeyType(): ?int
    {
        return $this->keyType;
    }

    /**
     * @param int $keyType
     *
     * @return HistoryConfiguration
     */
    public function setKeyType(int $keyType = null): HistoryConfiguration
    {
        $this->keyType = $keyType;
        return $this;
    }

    /**
     * @return HistoryConfigurationI
     */
    public function getHistoryConfiguration(): ?HistoryConfigurationI
    {
        return $this->historyConfiguration;
    }

    /**
     * @param HistoryConfigurationI $historyConfiguration
     *
     * @return HistoryConfiguration
     */
    public function setHistoryConfiguration(HistoryConfigurationI $historyConfiguration = null): HistoryConfiguration
    {
        $this->historyConfiguration = $historyConfiguration;
        return $this;
    }

    /**
     * @return HistoryConfigurationMenu
     */
    public function getHistoryConfigurationMenu(): ?HistoryConfigurationMenu
    {
        return $this->historyConfigurationMenu;
    }

    /**
     * @param HistoryConfigurationMenu $historyConfigurationMenu
     *
     * @return HistoryConfiguration
     */
    public function setHistoryConfigurationMenu(HistoryConfigurationMenu $historyConfigurationMenu = null):
    HistoryConfiguration
    {
        $this->historyConfigurationMenu = $historyConfigurationMenu;
        return $this;
    }

    /**
     * @return array
     */
    public function getKeyOptions(): array
    {
        return $this->keyOptions;
    }

    /**
     * @param array $keyOptions
     *
     * @return HistoryConfiguration
     */
    public function setKeyOptions(array $keyOptions): HistoryConfiguration
    {
        $this->keyOptions = $keyOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getStsProductionLocations(): array
    {
        return $this->stsProductionLocations;
    }

    /**
     * @param array $stsProductionLocations
     *
     * @return HistoryConfiguration
     */
    public function setStsProductionLocations(array $stsProductionLocations): HistoryConfiguration
    {
        $this->stsProductionLocations = $stsProductionLocations;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfigurationColors(): array
    {
        return $this->configurationColors;
    }

    /**
     * @param array $configurationColors
     *
     * @return HistoryConfiguration
     */
    public function setConfigurationColors(array $configurationColors): HistoryConfiguration
    {
        $this->configurationColors = $configurationColors;
        return $this;
    }

    /**
     * @return array
     */
    public function getReleaseStates(): array
    {
        return $this->releaseStates;
    }

    /**
     * @param array $releaseStates
     *
     * @return HistoryConfiguration
     */
    public function setReleaseStates(array $releaseStates): HistoryConfiguration
    {
        $this->releaseStates = $releaseStates;
        return $this;
    }

    /**
     * @return array
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    /**
     * @param array $users
     *
     * @return HistoryConfiguration
     */
    public function setUsers(array $users = null): HistoryConfiguration
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return array
     */
    public function getEcus(): array
    {
        return $this->ecus;
    }

    /**
     * @param array $ecus
     *
     * @return HistoryConfiguration
     */
    public function setEcus(array $ecus): HistoryConfiguration
    {
        $this->ecus = $ecus;
        return $this;
    }
}