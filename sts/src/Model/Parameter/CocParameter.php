<?php

namespace App\Model\Parameter;

use App\Model\ConvertibleToHistoryI;

class CocParameter implements ConvertibleToHistoryI
{
    /**
     * @var int
     */
    private $cocParameterId;

    /**
     * @var string
     */
    private $cocParameterName;

    /**
     * @var int
     */
    private $variableTypeId;

    /**
     * @var string
     */
    private $variableTypeName;

    /**
     * @var int
     */
    private $responsibleUserId;

    /**
     * @var string
     */
    private $responsibleUser;

    /**
     * @var string
     */
    private $unitName;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $field;

    /**
     * @var int
     */
    private $cocParameterValueSetId;

    /**
     * @var string
     */
    private $valueString;

    /**
     * @var \DateTime
     */
    private $valueDate;

    /**
     * @var boolean
     */
    private $valueBool;

    /**
     * @var double
     */
    private $valueDouble;

    /**
     * @var int
     */
    private $valueInteger;

    /**
     * @var int
     */
    private $valueBigInteger;

    /**
     * @var string
     */
    private $valueHex;

    /**
     * @var int
     */
    private $counter;

    /**
     * @return int
     */
    public function getCocParameterId(): ?int
    {
        return $this->cocParameterId;
    }

    /**
     * @param int $cocParameterId
     *
     * @return CocParameter
     */
    public function setCocParameterId(int $cocParameterId = null): CocParameter
    {
        $this->cocParameterId = $cocParameterId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCocParameterName(): ?string
    {
        return $this->cocParameterName;
    }

    /**
     * @param string $cocParameterName
     *
     * @return CocParameter
     */
    public function setCocParameterName(string $cocParameterName = null): CocParameter
    {
        $this->cocParameterName = $cocParameterName;

        return $this;
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
     *
     * @return CocParameter
     */
    public function setVariableTypeId(int $variableTypeId = null): CocParameter
    {
        $this->variableTypeId = $variableTypeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariableTypeName(): ?string
    {
        return $this->variableTypeName;
    }

    /**
     * @param string $variableTypeName
     *
     * @return CocParameter
     */
    public function setVariableTypeName(string $variableTypeName = null): CocParameter
    {
        $this->variableTypeName = $variableTypeName;

        return $this;
    }

    /**
     * @return int
     */
    public function getResponsibleUserId(): ?int
    {
        return $this->responsibleUserId;
    }

    /**
     * @param int $responsibleUserId
     *
     * @return CocParameter
     */
    public function setResponsibleUserId(int $responsibleUserId = null): CocParameter
    {
        $this->responsibleUserId = $responsibleUserId;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponsibleUser(): ?string
    {
        return $this->responsibleUser;
    }

    /**
     * @param string $responsibleUser
     *
     * @return CocParameter
     */
    public function setResponsibleUser(string $responsibleUser = null): CocParameter
    {
        $this->responsibleUser = $responsibleUser;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnitName(): ?string
    {
        return $this->unitName;
    }

    /**
     * @param string $unitName
     *
     * @return CocParameter
     */
    public function setUnitName(string $unitName = null): CocParameter
    {
        $this->unitName = $unitName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return CocParameter
     */
    public function setDescription(string $description = null): CocParameter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return CocParameter
     */
    public function setField(string $field = null): CocParameter
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return int
     */
    public function getCocParameterValueSetId(): ?int
    {
        return $this->cocParameterValueSetId;
    }


    /**
     * @param int $cocParameterValueSetId
     *
     * @return CocParameter
     */
    public function setCocParameterValueSetId(int $cocParameterValueSetId = null): CocParameter
    {
        $this->cocParameterValueSetId = $cocParameterValueSetId;

        return $this;
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
     *
     * @return CocParameter
     */
    public function setValueString(string $valueString = null): CocParameter
    {
        $this->valueString = $valueString;

        return $this;
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
     *
     * @return CocParameter
     */
    public function setValueBool(bool $valueBool = null): CocParameter
    {
        $this->valueBool = $valueBool;

        return $this;
    }

    /**
     * @return float
     */
    public function getValueDouble(): ?float
    {
        return $this->valueDouble;
    }

    /**
     * @param float $valueDouble
     *
     * @return CocParameter
     */
    public function setValueDouble(float $valueDouble = null): CocParameter
    {
        $this->valueDouble = $valueDouble;

        return $this;
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
     *
     * @return CocParameter
     */
    public function setValueInteger(int $valueInteger = null): CocParameter
    {
        $this->valueInteger = $valueInteger;

        return $this;
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
     *
     * @return CocParameter
     */
    public function setValueHex(string $valueHex = null): CocParameter
    {
        $this->valueHex = $valueHex;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueBigInteger(): ?int
    {
        return $this->valueBigInteger;
    }

    /**
     * @param int $valueBigInteger
     *
     * @return CocParameter
     */
    public function setValueBigInteger(int $valueBigInteger = null): CocParameter
    {
        $this->valueBigInteger = $valueBigInteger;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValueDate(): ?\DateTime
    {
        return $this->valueDate;
    }

    /**
     * @param \DateTime $valueDate
     *
     * @return CocParameter
     */
    public function setValueDate(\DateTime $valueDate = null): CocParameter
    {
        $this->valueDate = $valueDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getCounter(): ?int
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     *
     * @return CocParameter
     */
    public function setCounter(int $counter = null): CocParameter
    {
        $this->counter = $counter;
        return $this;
    }


}