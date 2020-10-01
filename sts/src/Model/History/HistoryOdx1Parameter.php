<?php

namespace App\Model\History;

use App\Model\History\HistoryI;
use App\Model\History\Traits\HistoryEvent;

class HistoryOdx1Parameter implements HistoryOdxParameter, HistoryI
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
    private $odx2;

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
    private $unit;

    /**
     * @var HistoryTuple
     */
    private $value;


    /**
     * @var HistoryTuple
     */
    private $valueString;

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
    private $valueBool;

    /**
     * @var HistoryTuple
     */
    private $linkingType;

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
     * @return HistoryTuple
     */
    public function getOdxSts02(): HistoryTuple
    {
        return $this->odxSts02;
    }

    /**
     * @param HistoryTuple $odxSts02
     *
     * @return HistoryOdx1Parameter
     */
    public function setOdxSts02(HistoryTuple $odxSts02): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setParameterId(HistoryTuple $parameterId): HistoryOdx1Parameter
    {
        $this->parameterId = $parameterId;
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
     * @return HistoryOdx1Parameter
     */
    public function setOrder(HistoryTuple $order): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setName(HistoryTuple $name): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setNameId(HistoryTuple $nameId): HistoryOdx1Parameter
    {
        $this->nameId = $nameId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOdx2(): HistoryTuple
    {
        return $this->odx2;
    }

    /**
     * @param HistoryTuple $odx2
     *
     * @return HistoryOdx1Parameter
     */
    public function setOdx2(HistoryTuple $odx2): HistoryOdx1Parameter
    {
        $this->odx2 = $odx2;
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
     * @return HistoryOdx1Parameter
     */
    public function setRead(HistoryTuple $read): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setWrite(HistoryTuple $write): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setConfirm(HistoryTuple $confirm): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setVariableType(HistoryTuple $variableType): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setType(HistoryTuple $type): HistoryOdx1Parameter
    {
        $this->type = $type;
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
     * @return HistoryOdx1Parameter
     */
    public function setUnit(HistoryTuple $unit): HistoryOdx1Parameter
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getValue(): HistoryTuple
    {
        return $this->value;
    }

    /**
     * @param HistoryTuple $value
     *
     * @return HistoryOdx1Parameter
     */
    public function setValue(HistoryTuple $value): HistoryOdx1Parameter
    {
        $this->value = $value;
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
     * @return HistoryOdx1Parameter
     */
    public function setValueString(HistoryTuple $valueString): HistoryOdx1Parameter
    {
        $this->valueString = $valueString;
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
     * @return HistoryOdx1Parameter
     */
    public function setValueInteger(HistoryTuple $valueInteger): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setValueUnsigned(HistoryTuple $valueUnsigned): HistoryOdx1Parameter
    {
        $this->valueUnsigned = $valueUnsigned;
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
     * @return HistoryOdx1Parameter
     */
    public function setValueBool(HistoryTuple $valueBool): HistoryOdx1Parameter
    {
        $this->valueBool = $valueBool;
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
     * @return HistoryOdx1Parameter
     */
    public function setLinkingType(HistoryTuple $linkingType): HistoryOdx1Parameter
    {
        $this->linkingType = $linkingType;
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
     * @return HistoryOdx1Parameter
     */
    public function setLinkedToGlobalParameter(HistoryTuple $linkedToGlobalParameter): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setDynamicParameterValuesByDiagnosticSoftware(HistoryTuple $dynamicParameterValuesByDiagnosticSoftware): HistoryOdx1Parameter
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
     * @return HistoryOdx1Parameter
     */
    public function setLinkedValueName(HistoryTuple $linkedValueName): HistoryOdx1Parameter
    {
        $this->linkedValueName = $linkedValueName;
        return $this;
    }
}