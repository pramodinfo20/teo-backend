<?php

namespace App\Model\History;

use App\Model\History\HistoryI;
use App\Model\History\Traits\HistoryEvent;

class HistoryOdx2Parameter implements HistoryOdxParameter, HistoryI
{
    use HistoryEvent;

    /**
     * @var HistoryTuple
     */
    private $odxSts02;

    /**
     * @var HistoryTuple
     */
    private $parameterId;

    /**
     * @var HistoryTuple
     */
    private $activated;

    /**
     * @var HistoryTuple
     */
    private $order;

    /**
     * @var HistoryTuple
     */
    private $name;

    /**
     * @var HistoryTuple
     */
    private $nameId;

    /**
     * @var HistoryTuple
     */
    private $odx1;

    /**
     * @var HistoryTuple
     */
    private $headerProtocol;

    /**
     * @var HistoryTuple
     */
    private $protocol;

    /**
     * @var HistoryTuple
     */
    private $read;

    /**
     * @var HistoryTuple
     */
    private $write;

    /**
     * @var HistoryTuple
     */
    private $confirm;

    /**
     * @var HistoryTuple
     */
    private $variableType;

    /**
     * @var HistoryTuple
     */
    private $type;

    /**
     * @var HistoryTuple
     */
    private $bytes;

    /**
     * @var HistoryTuple
     */
    private $factor;

    /**
     * @var HistoryTuple
     */
    private $offset;

    /**
     * @var HistoryTuple
     */
    private $unit;

    /**
     * @var HistoryTuple
     */
    private $valueString;

    /**
     * @var HistoryTuple
     */
    private $valueBool;

    /**
     * @var HistoryTuple
     */
    private $valueInteger;

    /**
     * @var HistoryTuple
     */
    private $valueUnsigned;

    /**
     * @var HistoryTuple
     */
    private $valueBlob;

    /**
     * @var HistoryTuple
     */
    private $linkingType;

    /**
     * @var HistoryTuple
     */
    private $linkingTypeName;

    /**
     * @var HistoryTuple
     */
    private $startBit;

    /**
     * @var HistoryTuple
     */
    private $stopBit;

    /**
     * @var HistoryTuple
     */
    private $udsId;

    /**
     * @var HistoryTuple
     */
    private $serialState;

    /**
     * @var HistoryTuple
     */
    private $linkedToGlobalParameter;

    /**
     * @var HistoryTuple
     */
    private $dynamicParameterValuesByDiagnosticSoftware;

    /**
     * @var HistoryTuple
     */
    private $linkedValueName;

    /**
     * @var HistoryTuple
     */
    private $valueHex;

    /**
     * @var HistoryTuple
     */
    private $coding;

    /**
     * @var HistoryTuple
     */
    private $bigEndian;

    /**
     * @return HistoryTuple
     */
    public function getOdxSts02(): HistoryTuple
    {
        return $this->odxSts02;
    }

    /**
     * @param HistoryTuple $odxSts02
     *
     * @return HistoryOdx2Parameter
     */
    public function setOdxSts02(HistoryTuple $odxSts02): HistoryOdx2Parameter
    {
        $this->odxSts02 = $odxSts02;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getParameterId(): HistoryTuple
    {
        return $this->parameterId;
    }

    /**
     * @param HistoryTuple $parameterId
     *
     * @return HistoryOdx2Parameter
     */
    public function setParameterId(HistoryTuple $parameterId): HistoryOdx2Parameter
    {
        $this->parameterId = $parameterId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getActivated(): HistoryTuple
    {
        return $this->activated;
    }

    /**
     * @param HistoryTuple $activated
     *
     * @return HistoryOdx2Parameter
     */
    public function setActivated(HistoryTuple $activated): HistoryOdx2Parameter
    {
        $this->activated = $activated;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOrder(): HistoryTuple
    {
        return $this->order;
    }

    /**
     * @param HistoryTuple $order
     *
     * @return HistoryOdx2Parameter
     */
    public function setOrder(HistoryTuple $order): HistoryOdx2Parameter
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getName(): HistoryTuple
    {
        return $this->name;
    }

    /**
     * @param HistoryTuple $name
     *
     * @return HistoryOdx2Parameter
     */
    public function setName(HistoryTuple $name): HistoryOdx2Parameter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getNameId(): HistoryTuple
    {
        return $this->nameId;
    }

    /**
     * @param HistoryTuple $nameId
     *
     * @return HistoryOdx2Parameter
     */
    public function setNameId(HistoryTuple $nameId): HistoryOdx2Parameter
    {
        $this->nameId = $nameId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOdx1(): HistoryTuple
    {
        return $this->odx1;
    }

    /**
     * @param HistoryTuple $odx1
     *
     * @return HistoryOdx2Parameter
     */
    public function setOdx1(HistoryTuple $odx1): HistoryOdx2Parameter
    {
        $this->odx1 = $odx1;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getHeaderProtocol(): HistoryTuple
    {
        return $this->headerProtocol;
    }

    /**
     * @param HistoryTuple $headerProtocol
     *
     * @return HistoryOdx2Parameter
     */
    public function setHeaderProtocol(HistoryTuple $headerProtocol): HistoryOdx2Parameter
    {
        $this->headerProtocol = $headerProtocol;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getProtocol(): HistoryTuple
    {
        return $this->protocol;
    }

    /**
     * @param HistoryTuple $protocol
     *
     * @return HistoryOdx2Parameter
     */
    public function setProtocol(HistoryTuple $protocol): HistoryOdx2Parameter
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRead(): HistoryTuple
    {
        return $this->read;
    }

    /**
     * @param HistoryTuple $read
     *
     * @return HistoryOdx2Parameter
     */
    public function setRead(HistoryTuple $read): HistoryOdx2Parameter
    {
        $this->read = $read;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getWrite(): HistoryTuple
    {
        return $this->write;
    }

    /**
     * @param HistoryTuple $write
     *
     * @return HistoryOdx2Parameter
     */
    public function setWrite(HistoryTuple $write): HistoryOdx2Parameter
    {
        $this->write = $write;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getConfirm(): HistoryTuple
    {
        return $this->confirm;
    }

    /**
     * @param HistoryTuple $confirm
     *
     * @return HistoryOdx2Parameter
     */
    public function setConfirm(HistoryTuple $confirm): HistoryOdx2Parameter
    {
        $this->confirm = $confirm;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getVariableType(): HistoryTuple
    {
        return $this->variableType;
    }

    /**
     * @param HistoryTuple $variableType
     *
     * @return HistoryOdx2Parameter
     */
    public function setVariableType(HistoryTuple $variableType): HistoryOdx2Parameter
    {
        $this->variableType = $variableType;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getType(): HistoryTuple
    {
        return $this->type;
    }

    /**
     * @param HistoryTuple $type
     *
     * @return HistoryOdx2Parameter
     */
    public function setType(HistoryTuple $type): HistoryOdx2Parameter
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getBytes(): HistoryTuple
    {
        return $this->bytes;
    }

    /**
     * @param HistoryTuple $bytes
     *
     * @return HistoryOdx2Parameter
     */
    public function setBytes(HistoryTuple $bytes): HistoryOdx2Parameter
    {
        $this->bytes = $bytes;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getFactor(): HistoryTuple
    {
        return $this->factor;
    }

    /**
     * @param HistoryTuple $factor
     *
     * @return HistoryOdx2Parameter
     */
    public function setFactor(HistoryTuple $factor): HistoryOdx2Parameter
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOffset(): HistoryTuple
    {
        return $this->offset;
    }

    /**
     * @param HistoryTuple $offset
     *
     * @return HistoryOdx2Parameter
     */
    public function setOffset(HistoryTuple $offset): HistoryOdx2Parameter
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getUnit(): HistoryTuple
    {
        return $this->unit;
    }

    /**
     * @param HistoryTuple $unit
     *
     * @return HistoryOdx2Parameter
     */
    public function setUnit(HistoryTuple $unit): HistoryOdx2Parameter
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueString(): HistoryTuple
    {
        return $this->valueString;
    }

    /**
     * @param HistoryTuple $valueString
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueString(HistoryTuple $valueString): HistoryOdx2Parameter
    {
        $this->valueString = $valueString;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueBool(): HistoryTuple
    {
        return $this->valueBool;
    }

    /**
     * @param HistoryTuple $valueBool
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueBool(HistoryTuple $valueBool): HistoryOdx2Parameter
    {
        $this->valueBool = $valueBool;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueInteger(): HistoryTuple
    {
        return $this->valueInteger;
    }

    /**
     * @param HistoryTuple $valueInteger
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueInteger(HistoryTuple $valueInteger): HistoryOdx2Parameter
    {
        $this->valueInteger = $valueInteger;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueUnsigned(): HistoryTuple
    {
        return $this->valueUnsigned;
    }

    /**
     * @param HistoryTuple $valueUnsigned
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueUnsigned(HistoryTuple $valueUnsigned): HistoryOdx2Parameter
    {
        $this->valueUnsigned = $valueUnsigned;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueBlob(): HistoryTuple
    {
        return $this->valueBlob;
    }

    /**
     * @param HistoryTuple $valueBlob
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueBlob(HistoryTuple $valueBlob): HistoryOdx2Parameter
    {
        $this->valueBlob = $valueBlob;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getLinkingType(): HistoryTuple
    {
        return $this->linkingType;
    }

    /**
     * @param HistoryTuple $linkingType
     *
     * @return HistoryOdx2Parameter
     */
    public function setLinkingType(HistoryTuple $linkingType): HistoryOdx2Parameter
    {
        $this->linkingType = $linkingType;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getLinkingTypeName(): ?HistoryTuple
    {
        return $this->linkingTypeName;
    }

    /**
     * @param HistoryTuple $linkingTypeName
     *
     * @return HistoryOdx2Parameter
     */
    public function setLinkingTypeName(HistoryTuple $linkingTypeName = null): HistoryOdx2Parameter
    {
        $this->linkingTypeName = $linkingTypeName;
        return $this;
    }


    /**
     * @return HistoryTuple
     */
    public function getStartBit(): HistoryTuple
    {
        return $this->startBit;
    }

    /**
     * @param HistoryTuple $startBit
     *
     * @return HistoryOdx2Parameter
     */
    public function setStartBit(HistoryTuple $startBit): HistoryOdx2Parameter
    {
        $this->startBit = $startBit;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStopBit(): HistoryTuple
    {
        return $this->stopBit;
    }

    /**
     * @param HistoryTuple $stopBit
     *
     * @return HistoryOdx2Parameter
     */
    public function setStopBit(HistoryTuple $stopBit): HistoryOdx2Parameter
    {
        $this->stopBit = $stopBit;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getUdsId(): HistoryTuple
    {
        return $this->udsId;
    }

    /**
     * @param HistoryTuple $udsId
     *
     * @return HistoryOdx2Parameter
     */
    public function setUdsId(HistoryTuple $udsId): HistoryOdx2Parameter
    {
        $this->udsId = $udsId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSerialState(): HistoryTuple
    {
        return $this->serialState;
    }

    /**
     * @param HistoryTuple $serialState
     *
     * @return HistoryOdx2Parameter
     */
    public function setSerialState(HistoryTuple $serialState): HistoryOdx2Parameter
    {
        $this->serialState = $serialState;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getLinkedToGlobalParameter(): HistoryTuple
    {
        return $this->linkedToGlobalParameter;
    }

    /**
     * @param HistoryTuple $linkedToGlobalParameter
     *
     * @return HistoryOdx2Parameter
     */
    public function setLinkedToGlobalParameter(HistoryTuple $linkedToGlobalParameter): HistoryOdx2Parameter
    {
        $this->linkedToGlobalParameter = $linkedToGlobalParameter;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getDynamicParameterValuesByDiagnosticSoftware(): HistoryTuple
    {
        return $this->dynamicParameterValuesByDiagnosticSoftware;
    }

    /**
     * @param HistoryTuple $dynamicParameterValuesByDiagnosticSoftware
     *
     * @return HistoryOdx2Parameter
     */
    public function setDynamicParameterValuesByDiagnosticSoftware(HistoryTuple $dynamicParameterValuesByDiagnosticSoftware): HistoryOdx2Parameter
    {
        $this->dynamicParameterValuesByDiagnosticSoftware = $dynamicParameterValuesByDiagnosticSoftware;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getLinkedValueName(): HistoryTuple
    {
        return $this->linkedValueName;
    }

    /**
     * @param HistoryTuple $linkedValueName
     *
     * @return HistoryOdx2Parameter
     */
    public function setLinkedValueName(HistoryTuple $linkedValueName): HistoryOdx2Parameter
    {
        $this->linkedValueName = $linkedValueName;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValueHex(): HistoryTuple
    {
        return $this->valueHex;
    }

    /**
     * @param HistoryTuple $valueHex
     *
     * @return HistoryOdx2Parameter
     */
    public function setValueHex(HistoryTuple $valueHex): HistoryOdx2Parameter
    {
        $this->valueHex = $valueHex;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getCoding(): HistoryTuple
    {
        return $this->coding;
    }

    /**
     * @param HistoryTuple $coding
     *
     * @return HistoryOdx2Parameter
     */
    public function setCoding(HistoryTuple $coding): HistoryOdx2Parameter
    {
        $this->coding = $coding;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getBigEndian(): HistoryTuple
    {
        return $this->bigEndian;
    }

    /**
     * @param HistoryTuple $bigEndian
     *
     * @return HistoryOdx2Parameter
     */
    public function setBigEndian(HistoryTuple $bigEndian): HistoryOdx2Parameter
    {
        $this->bigEndian = $bigEndian;
        return $this;
    }
}