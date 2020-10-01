<?php


namespace App\Model\History;


class HistorySubConfiguration
{
    /**
     * @var int
     */
    private $subConfigurationId;

    /**
     * @var string
     */
    private $subConfigurationName;

    /**
     * @return int
     */
    public function getSubConfigurationId(): int
    {
        return $this->subConfigurationId;
    }

    /**
     * @param int $subConfigurationId
     *
     * @return HistorySubConfiguration
     */
    public function setSubConfigurationId(int $subConfigurationId): HistorySubConfiguration
    {
        $this->subConfigurationId = $subConfigurationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubConfigurationName(): string
    {
        return $this->subConfigurationName;
    }

    /**
     * @param string $subConfigurationName
     *
     * @return HistorySubConfiguration
     */
    public function setSubConfigurationName(string $subConfigurationName): HistorySubConfiguration
    {
        $this->subConfigurationName = $subConfigurationName;
        return $this;
    }
}