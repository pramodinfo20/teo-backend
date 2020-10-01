<?php

namespace App\Model\Parameter;

use App\Repository\GlobalParametersRepository;
use DateTime;

class GlobalParameter
{
    /**
     * @var int
     */
    private $globalParameterId;

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $variableTypeId;

    /**
     * @var int
     */
    private $globalParameterValueSetId;

    /**
     * @var string
     */
    private $valueString;

    /**
     * @var boolean
     */
    private $valueBool;

    /**
     * @var int
     */
    private $valueInteger;

    /**
     * @var double
     */
    private $valueDouble;

    /**
     * @var int
     */
    private $valueUnsigned;

    /**
     * @var int
     */
    private $valueSigned;

    /**
     * @var int
     */
    private $valueBiginteger;

    /**
     * @var string
     */
    private $valueHex;

    /**
     * @var \DateTime
     */
    private $valueDate;

    /**
     * @return int
     */
    public function getGlobalParameterId(): ?int
    {
        return $this->globalParameterId;
    }

    /**
     * @param int $globalParameterId
     * @return GlobalParameter
     */
    public function setGlobalParameterId(int $globalParameterId = null): GlobalParameter
    {
        $this->globalParameterId = $globalParameterId;

        return $this;
    }

    /**
     * @return int
     */
    public function getGlobalParameterValueSetId(): ?int
    {
        return $this->globalParameterValueSetId;
    }

    /**
     * @param int $globalParameterValueSetId
     *
     * @return GlobalParameter
     */
    public function setGlobalParameterValueSetId(int $globalParameterValueSetId = null): GlobalParameter
    {
        $this->globalParameterValueSetId = $globalParameterValueSetId;

        return $this;
    }

    /**
     * @return int
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @param int $min
     *
     * @return GlobalParameter
     */
    public function setMin(int $min = null): GlobalParameter
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * @param int $max
     *
     * @return GlobalParameter
     */
    public function setMax(int $max = null): GlobalParameter
    {
        $this->max = $max;

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
     * @return GlobalParameter
     */
    public function setValueString(string $valueString = null): GlobalParameter
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
     * @return GlobalParameter
     */
    public function setValueBool(bool $valueBool = null): GlobalParameter
    {
        $this->valueBool = $valueBool;

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
     * @return GlobalParameter
     */
    public function setValueInteger(int $valueInteger = null): GlobalParameter
    {
        $this->valueInteger = $valueInteger;

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
     * @return GlobalParameter
     */
    public function setValueDouble(float $valueDouble = null): GlobalParameter
    {
        $this->valueDouble = $valueDouble;

        return $this;
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
     * @return GlobalParameter
     */
    public function setValueUnsigned(int $valueUnsigned = null): GlobalParameter
    {
        $this->valueUnsigned = $valueUnsigned;

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
     * @return GlobalParameter
     */
    public function setValueHex(string $valueHex = null): GlobalParameter
    {
        $this->valueHex = $valueHex;

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
     * @return GlobalParameter
     */
    public function setValueDate(\DateTime $valueDate = null): GlobalParameter
    {
        $this->valueDate = $valueDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getValueSigned(): ?int
    {
        return $this->valueSigned;
    }

    /**
     * @param int $valueSigned
     * @return GlobalParameter
     */
    public function setValueSigned(int $valueSigned = null): GlobalParameter
    {
        $this->valueSigned = $valueSigned;
        return $this;
    }

    /**
     * @return int
     */
    public function getValueBiginteger(): ?int
    {
        return $this->valueBiginteger;
    }

    /**
     * @param int $valueBiginteger
     * @return GlobalParameter
     */
    public function setValueBiginteger(int $valueBiginteger = null): GlobalParameter
    {
        $this->valueBiginteger = $valueBiginteger;
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
     * @return GlobalParameter
     */
    public function setVariableTypeId(int $variableTypeId = null): GlobalParameter
    {
        $this->variableTypeId = $variableTypeId;
        return $this;
    }


}