<?php

namespace App\Model;

use App\Entity\CocParameters;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\GlobalParameters;
use App\Enum\Entity\EcuSwParameterTypes;

class Odx1Parameter implements OdxParameter, ComparableI, EqualI, ConvertibleToHistoryI
{
    /**
     * @var bool
     */
    private $odxSts02;

    /**
     * @var int
     */
    private $parameterId;

    /**
     * @var int
     */
    private $order;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $nameId;

    /**
     * @var bool
     */
    private $odx2;

    /**
     * @var bool
     */
    private $read;

    /**
     * @var bool
     */
    private $write;

    /**
     * @var bool
     */
    private $confirm;

    /**
     * @var string
     */
    private $variableType;
    /**
     * @var int
     */
    private $type;


    /**
     * @var string
     */
    private $unit;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $valueString;

    /**
     * @var int
     */
    private $valueInteger;

    /**
     * @var int
     */
    private $valueUnsigned;

    /**
     * @var bool
     */
    private $valueBool;

    /**
     * @var string
     */
    private $linkingType;

    /**
     * @var GlobalParameters
     */
    private $linkedToGlobalParameter;

    /**
     * @var DynamicParameterValuesByDiagnosticSoftware
     */
    private $dynamicParameterValuesByDiagnosticSoftware;

    /**
     * @var string
     */
    private $linkedValueName;

    /**
     * @return bool
     */
    public function isOdxSts02(): ?bool {
        return $this->odxSts02;
    }

    /**
     * @param bool $odxSts02
     */
    public function setOdxSts02(bool $odxSts02 = null): void {
        $this->odxSts02 = $odxSts02;
    }

    /**
     * @return int
     */
    public function getParameterId(): ?int
    {
        return $this->parameterId;
    }

    /**
     * @param int $parameterId
     */
    public function setParameterId(int $parameterId = null): void
    {
        $this->parameterId = $parameterId;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getNameId(): ?int
    {
        return $this->nameId;
    }

    /**
     * @param int $nameId
     */
    public function setNameId(int $nameId = null): void
    {
        $this->nameId = $nameId;
    }

    /**
     * @return bool
     */
    public function isOdx2(): bool
    {
        return $this->odx2;
    }

    /**
     * @param bool $odx2
     */
    public function setOdx2(bool $odx2): void
    {
        $this->odx2 = $odx2;
    }

    /**
     * @return bool
     */
    public function isRead(): ?bool
    {
        return $this->read;
    }

    /**
     * @param bool $read
     */
    public function setRead(bool $read = null): void
    {
        $this->read = $read;
    }

    /**
     * @return bool
     */
    public function isWrite(): ?bool
    {
        return $this->write;
    }

    /**
     * @param bool $write
     */
    public function setWrite(bool $write = null): void
    {
        $this->write = $write;
    }

    /**
     * @return bool
     */
    public function isConfirm(): ?bool
    {
        return $this->confirm;
    }

    /**
     * @param bool $confirm
     */
    public function setConfirm(bool $confirm = null): void
    {
        $this->confirm = $confirm;
    }

    /**
     * @return string
     */
    public function getVariableType(): string
    {
        return $this->variableType;
    }

    /**
     * @param string $variableType
     */
    public function setVariableType(string $variableType): void
    {
        $this->variableType = $variableType;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit(string $unit = null): void
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    /**
     * @param string $valueString
     */
    public function setValueString(string $valueString = null): void
    {
        $this->valueString = $valueString;
    }

    /**
     * @return int
     */
    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    /**
     * @param int $valueInteger
     */
    public function setValueInteger(int $valueInteger = null): void
    {
        $this->valueInteger = $valueInteger;
    }

    /**
     * @return int
     */
    public function getValueUnsigned(): ?int
    {
        return $this->valueUnsigned;
    }

    /**
     * @param int $valueUnsigned
     */
    public function setValueUnsigned(int $valueUnsigned = null): void
    {
        $this->valueUnsigned = $valueUnsigned;
    }

    /**
     * @return bool
     */
    public function isValueBool(): ?bool
    {
        return $this->valueBool;
    }

    /**
     * @param bool $valueBool
     */
    public function setValueBool(bool $valueBool = null): void
    {
        $this->valueBool = $valueBool;
    }

    /**
     * @return string
     */
    public function getLinkingType(): string
    {
        return $this->linkingType;
    }

    /**
     * @param string $linkingType
     */
    public function setLinkingType(string $linkingType): void
    {
        $this->linkingType = $linkingType;
    }

    /**
     * @return GlobalParameters
     */
    public function getLinkedToGlobalParameter(): ?GlobalParameters
    {
        return $this->linkedToGlobalParameter;
    }

    /**
     * @param GlobalParameters $linkedToGlobalParameter
     */
    public function setLinkedToGlobalParameter(GlobalParameters $linkedToGlobalParameter = null): void
    {
        $this->linkedToGlobalParameter = $linkedToGlobalParameter;
    }

    /**
     * @return DynamicParameterValuesByDiagnosticSoftware
     */
    public function getDynamicParameterValuesByDiagnosticSoftware(): ?DynamicParameterValuesByDiagnosticSoftware
    {
        return $this->dynamicParameterValuesByDiagnosticSoftware;
    }

    /**
     * @param DynamicParameterValuesByDiagnosticSoftware $dynamicParameterValues
     */
    public function setDynamicParameterValuesByDiagnosticSoftware(DynamicParameterValuesByDiagnosticSoftware $dynamicParameterValues = null): void
    {
        $this->dynamicParameterValuesByDiagnosticSoftware = $dynamicParameterValues;
    }

    /**
     * @return string
     */
    public function getLinkedValueName(): ?string
    {
        return $this->linkedValueName;
    }

    /**
     * @param string $linkedValueName
     */
    public function setLinkedValueName(string $linkedValueName = null): void
    {
        $this->linkedValueName = $linkedValueName;
    }

    /** Like a Spaceship - <=> - 0 - equal, -1 - left < right, 1 left > right
     *
     * @param ComparableI $interface
     *
     * @return int
     */
    public function compare(ComparableI $interface): int
    {
        return ($this->getOrder() - 4) <=> ($interface->getOrder() - 4);
    }

    public function equals(EqualI $interface): bool
    {
        return $this->getParameterId() == $interface->getParameterId();
    }
}