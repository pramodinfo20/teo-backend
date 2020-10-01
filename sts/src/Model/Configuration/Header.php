<?php

namespace App\Model\Configuration;

use App\Entity\OdxSourceTypes;
use App\Model\ConvertibleToHistoryI;
use App\Model\Header as BaseHeader;

class Header extends BaseHeader implements ConvertibleToHistoryI
{
    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $penta;


    /**
     * @var string
     */
    private $ecuName;

    /**
     * @var OdxSourceTypes
     */
    private $odxSourceType;

    /**
     * @var String
     */
    private $odxSourceTypeName;

    /**
     * @var string
     */
    private $clonedFrom;

    /**
     * @return string
     */
    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     */
    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getEcuName(): string
    {
        return $this->ecuName;
    }

    /**
     * @param string $ecuName
     */
    public function setEcuName(string $ecuName): void
    {
        $this->ecuName = $ecuName;
    }

    /**
     * @return string
     */
    public function getClonedFrom(): ?string
    {
        return $this->clonedFrom;
    }

    /**
     * @param string $clonedFrom
     */
    public function setClonedFrom(string $clonedFrom = null): void
    {
        $this->clonedFrom = $clonedFrom;
    }

    /**
     * @return string
     */
    public function getPenta(): string
    {
        return $this->penta;
    }

    /**
     * @param string $penta
     */
    public function setPenta(string $penta): void
    {
        $this->penta = $penta;
    }

    /**
     * @return OdxSourceTypes
     */
    public function getOdxSourceType(): OdxSourceTypes
    {
        return $this->odxSourceType;
    }

    /**
     * @param OdxSourceTypes $odxSourceType
     */
    public function setOdxSourceType(OdxSourceTypes $odxSourceType): void
    {
        $this->odxSourceType = $odxSourceType;
    }

    /**
     * @return String
     */
    public function getOdxSourceTypeName(): String
    {
        return $this->odxSourceTypeName;
    }

    /**
     * @param String $odxSourceTypeName
     */
    public function setOdxSourceTypeName(String $odxSourceTypeName): void
    {
        $this->odxSourceTypeName = $odxSourceTypeName;
    }
}