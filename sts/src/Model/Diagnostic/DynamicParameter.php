<?php

namespace App\Model\Diagnostic;

class DynamicParameter
{
    /**
     * @var int
     */
    private $parameterId;

    /**
     * @var string
     */
    private $value;

    /**
     * @return int
     */
    public function getParameterId(): ?int
    {
        return $this->parameterId;
    }

    /**
     * @param int $parameterId
     *
     * @return DynamicParameter
     */
    public function setParameterId(int $parameterId = null): DynamicParameter
    {
        $this->parameterId = $parameterId;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return DynamicParameter
     */
    public function setValue(string $value = null): DynamicParameter
    {
        $this->value = $value;
        return $this;
    }
}