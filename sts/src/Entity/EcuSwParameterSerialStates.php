<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterSerialStates
 *
 * @ORM\Table(name="ecu_sw_parameter_serial_states")
 * @ORM\Entity
 */
class EcuSwParameterSerialStates
{
    /**
     * @var bool
     *
     * @ORM\Column(name="serial_state", type="boolean", nullable=false)
     */
    private $serialState;

    /**
     * @var EcuSwParameters
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EcuSwParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_parameter_id", referencedColumnName="ecu_sw_parameter_id")
     * })
     */
    private $ecuSwParameter;

    public function getSerialState(): ?bool
    {
        return $this->serialState;
    }

    public function setSerialState(bool $serialState): self
    {
        $this->serialState = $serialState;

        return $this;
    }

    public function getEcuSwParameter(): ?EcuSwParameters
    {
        return $this->ecuSwParameter;
    }

    public function setEcuSwParameter(?EcuSwParameters $ecuSwParameter): self
    {
        $this->ecuSwParameter = $ecuSwParameter;

        return $this;
    }


}
