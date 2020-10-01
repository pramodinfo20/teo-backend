<?php


namespace App\Model\History;


class HistoryConfigurationMenu
{
    /**
     * @var int
     */
    private $historyConfigurationId;

    /**
     * @var string
     */
    private $historyConfigurationName;

    /**
     * @var array
     */
    private $historySubConfigurations;

    /**
     * @return int
     */
    public function getHistoryConfigurationId(): int
    {
        return $this->historyConfigurationId;
    }

    /**
     * @param int $historyConfigurationId
     *
     * @return HistoryConfigurationMenu
     */
    public function setHistoryConfigurationId(int $historyConfigurationId): HistoryConfigurationMenu
    {
        $this->historyConfigurationId = $historyConfigurationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getHistoryConfigurationName(): string
    {
        return $this->historyConfigurationName;
    }

    /**
     * @param string $historyConfigurationName
     *
     * @return HistoryConfigurationMenu
     */
    public function setHistoryConfigurationName(string $historyConfigurationName): HistoryConfigurationMenu
    {
        $this->historyConfigurationName = $historyConfigurationName;
        return $this;
    }

    /**
     * @return array
     */
    public function getHistorySubConfigurations(): array
    {
        return $this->historySubConfigurations;
    }

    /**
     * @param array $historySubConfigurations
     *
     * @return HistoryConfigurationMenu
     */
    public function setHistorySubConfigurations(array $historySubConfigurations): HistoryConfigurationMenu
    {
        $this->historySubConfigurations = $historySubConfigurations;
        return $this;
    }

}