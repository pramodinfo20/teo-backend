<?php

namespace App\Model\Configuration;

use App\Model\Odx2Parameter as BaseOdx2Parameter;

final class Odx2Parameter extends BaseOdx2Parameter implements OdxParameter
{

    /**
     * @var int
     */
    private $overwrittenValueSetId;

    /**
     * @var int
     */
    private $variableTypeId;

    /**
     * @var string
     */
    private $linkingTypeName;


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
    public function getVariableTypeId(): ?int
    {
        return $this->variableTypeId;
    }

    /**
     * @param int $variableTypeId
     */
    public function setVariableTypeId(int $variableTypeId = null): void
    {
        $this->variableTypeId = $variableTypeId;
    }

    /**
     * @return string
     */
    public function getLinkingTypeName(): ?string
    {
        return $this->linkingTypeName;
    }

    /**
     * @param string $linkingTypeName
     */
    public function setLinkingTypeName(string $linkingTypeName = null): void
    {
        $this->linkingTypeName = $linkingTypeName;
    }
}