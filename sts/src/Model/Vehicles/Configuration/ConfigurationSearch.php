<?php

namespace App\Model\Vehicles\Configuration;

class ConfigurationSearch
{
    /**
     * @var int
     */
    public $configurationId;
    /**
     * @var string
     */
    public $type;
    /**
     * @var int
     */
    public $year;
    /**
     * @var string
     */
    public $series;
    /**
     * @var string|null
     */
    public $customerKey;

    /**
     * Parameter constructor.
     *
     * @param int           $configurationId
     * @param string        $type
     * @param int           $year
     * @param string        $series
     * @param string|null   $customerKey
     */
    public function __construct(
        int $configurationId,
        string $type,
        int $year,
        string $series,
        string $customerKey = null
    )
    {
        $this->configurationId = $configurationId;
        $this->type = $type;
        $this->year = ($year > 9) ? "$year" : "0$year";
        $this->series = $series;
        $this->customerKey = is_null($customerKey) ? "" : $customerKey;
    }
}