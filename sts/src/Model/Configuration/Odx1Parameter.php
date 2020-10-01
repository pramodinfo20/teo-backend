<?php

namespace App\Model\Configuration;

use App\Model\Odx1Parameter as BaseOdx1Parameter;

final class Odx1Parameter extends BaseOdx1Parameter implements OdxParameter
{
    /**
     * @var string
     */
    private $valueHex;

    /**
     * @var int
     */
    private $overwrittenValueSetId;

    /**
     * @var int
     */
    private $variableTypeId;

    /**
     * @var int
     */
    private $linkingTypeId;


    /**
     * @return string
     */
    public function getValueHex(): ?string
    {
        return $this->valueHex;
    }

    /**
     * @param string $valueHex
     */
    public function setValueHex(string $valueHex = null): void
    {
        $this->valueHex = $valueHex;
    }

    /**
     * @return int
     */
    public function getOverwrittenValueSetId(): ?int
    {
        return $this->overwrittenValueSetId;
    }

    /**
     * @param int $overwrittenValueSetId
     */
    public function setOverwrittenValueSetId(int $overwrittenValueSetId = null): void
    {
        $this->overwrittenValueSetId = $overwrittenValueSetId;
    }

    /**
     * @return int
     */
    public function getVariableTypeId(): int
    {
        return $this->variableTypeId;
    }

    /**
     * @param int $variableTypeId
     */
    public function setVariableTypeId(int $variableTypeId): void
    {
        $this->variableTypeId = $variableTypeId;
    }

    /**
     * @return int
     */
    public function getLinkingTypeId(): int
    {
        return $this->linkingTypeId;
    }

    /**
     * @param int $linkingTypeId
     */
    public function setLinkingTypeId(int $linkingTypeId): void
    {
        $this->linkingTypeId = $linkingTypeId;
    }

}