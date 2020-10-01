<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterSerialsStates
 *
 * @ORM\Table(name="ecu_sw_parameter_serials_states", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_serials_states_serial_id_unique", columns={"serial"})})
 * @ORM\Entity
 */
class EcuSwParameterSerialsStates
{
    /**
     * @var int
     *
     * @ORM\Column(name="fev_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_serials_states_fev_id_seq", allocationSize=1,
     *                                                                                   initialValue=1)
     */
    private $fevId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="serial_state", type="boolean", nullable=true)
     */
    private $serialState;

    /**
     * @var EcuSwParameters
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="serial", referencedColumnName="ecu_sw_parameter_id")
     * })
     */
    private $serial;

    public function getFevId(): ?int
    {
        return $this->fevId;
    }

    public function getSerialState(): ?bool
    {
        return $this->serialState;
    }

    public function setSerialState(?bool $serialState): self
    {
        $this->serialState = $serialState;

        return $this;
    }

    public function getSerial(): ?EcuSwParameters
    {
        return $this->serial;
    }

    public function setSerial(?EcuSwParameters $serial): self
    {
        $this->serial = $serial;

        return $this;
    }


}
