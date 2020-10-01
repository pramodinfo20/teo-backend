<?php

namespace App\Model;

use App\Entity\CocParameters;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\GlobalParameters;
use App\Enum\Entity\EcuSwParameterTypes;

class Odx2Parameter implements OdxParameter, ComparableI, EqualI, ConvertibleToHistoryI
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
     * @var bool
     */
    private $activated;

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
    private $odx1;

    /**
     * @var string
     */
    private $headerProtocol;

    /**
     * @var EcuCommunicationProtocols
     */
    private $protocol;

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
     * @var int
     */
    private $bytes;

    /**
     * @var float
     */
    private $factor;

    /**
     * @var float
     */
    private $offset;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var string
     */
    private $valueString;

    /**
     * @var bool
     */
    private $valueBool;

    /**
     * @var int
     */
    private $valueInteger;

    /**
     * @var int
     */
    private $valueUnsigned;

    /**
     * @var string
     */
    private $valueBlob;

    /**
     * @var int
     */
    private $linkingType;

    /**
     * @var string
     */
    private $linkingTypeName;

    /**
     * @var int
     */
    private $startBit;

    /**
     * @var int
     */
    private $stopBit;

    /**
     * @var string
     */
    private $udsId;

    /**
     * @var bool
     */
    private $serialState;

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
     * @var string
     */
    private $valueHex;

    /**
     * @var string
     */
    private $coding;

    /**
     * @var bool
     */
    private $bigEndian;

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
     * @return bool
     */
    public function isActivated(): ?bool
    {
        return $this->activated;
    }

    /**
     * @param bool $activated
     */
    public function setActivated(bool $activated = false): void
    {
        $this->activated = $activated;
    }

    /**
     * @return int
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int $order http://sts.localhost/ecu/sw/parameters/ecu/1/sw/4/odx/
     */
    public function setOrder(int $order = null): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null): void
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
    public function isOdx1(): ?bool
    {
        return $this->odx1;
    }

    /**
     * @param bool $odx1
     */
    public function setOdx1(bool $odx1 = null): void
    {
        $this->odx1 = $odx1;
    }

    /**
     * @return string
     */
    public function getHeaderProtocol(): ?string
    {
        return $this->headerProtocol;
    }

    /**
     * @param string $headerProtocol
     */
    public function setHeaderProtocol(string $headerProtocol = null): void
    {
        $this->headerProtocol = $headerProtocol;
    }

    /**
     * @return EcuCommunicationProtocols
     */
    public function getProtocol(): ?EcuCommunicationProtocols
    {
        return $this->protocol;
    }

    /**
     * @param $protocol
     */
    public function setProtocol(EcuCommunicationProtocols $protocol = null): void
    {
        $this->protocol = $protocol;
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
    public function getVariableType(): ?string
    {
        return $this->variableType;
    }

    /**
     * @param string $variableType
     */
    public function setVariableType(string $variableType = null): void
    {
        $this->variableType = $variableType;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type = null): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getBytes(): ?int
    {
        return $this->bytes;
    }

    /**
     * @param int $bytes
     */
    public function setBytes(int $bytes = null): void
    {
        $this->bytes = $bytes;
    }

    /**
     * @return float
     */
    public function getFactor(): ?float
    {
        return $this->factor;
    }

    /**
     * @param float $factor
     */
    public function setFactor(float $factor = null): void
    {
        $this->factor = $factor;
    }

    /**
     * @return float
     */
    public function getOffset(): ?float
    {
        return $this->offset;
    }

    /**
     * @param float $offset
     */
    public function setOffset(float $offset = null): void
    {
        $this->offset = $offset;
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
     * @return string
     */
    public function getValueBlob(): ?string
    {
        return $this->valueBlob;
    }

    /**
     * @param string $valueBlob
     */
    public function setValueBlob(string $valueBlob = null): void
    {
        $this->valueBlob = $valueBlob;
    }

    /**
     * @return int
     */
    public function getLinkingType(): ?int
    {
        return $this->linkingType;
    }

    /**
     * @param int $linkingType
     */
    public function setLinkingType(int $linkingType = null): void
    {
        $this->linkingType = $linkingType;
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

    /**
     * @return int
     */
    public function getStartBit(): ?int
    {
        return $this->startBit;
    }

    /**
     * @param int $startBit
     */
    public function setStartBit(int $startBit = null): void
    {
        $this->startBit = $startBit;
    }

    /**
     * @return int
     */
    public function getStopBit(): ?int
    {
        return $this->stopBit;
    }

    /**
     * @param int $stopBit
     */
    public function setStopBit(int $stopBit = null): void
    {
        $this->stopBit = $stopBit;
    }

    /**
     * @return string
     */
    public function getUdsId(): ?string
    {
        return $this->udsId;
    }

    /**
     * @param string $udsId
     */
    public function setUdsId(string $udsId = null): void
    {
        $this->udsId = $udsId;
    }

    /**
     * @return bool
     */
    public function getSerialState(): ?bool
    {
        return $this->serialState;
    }

    /**
     * @param bool $serialState
     */
    public function setSerialState(bool $serialState = null): void
    {
        $this->serialState = $serialState;
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
     * @return string
     */
    public function getCoding(): ?string
    {
        return $this->coding;
    }

    /**
     * @param string $coding
     */
    public function setCoding(string $coding = null): void
    {
        $this->coding = $coding;
    }

    /**
     * @return bool
     */
    public function isBigEndian(): ?bool
    {
        return $this->bigEndian;
    }

    /**
     * @param bool $bigEndian
     */
    public function setBigEndian(bool $bigEndian = null): void
    {
        $this->bigEndian = $bigEndian;
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