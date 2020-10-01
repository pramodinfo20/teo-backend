<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuParameters
 *
 * @ORM\Table(name="ecu_parameters", uniqueConstraints={@ORM\UniqueConstraint(name="unique_ecu_parameter_set_id_ecu_id", columns={"ecu_parameter_set_id", "ecu_id"})}, indexes={@ORM\Index(name="IDX_D84AD19D7AB8D155", columns={"ecu_parameter_set_id"}), @ORM\Index(name="IDX_D84AD19DF2887E5B", columns={"ecu_id"})})
 * @ORM\Entity
 */
class EcuParameters
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_parameters_ecu_parameter_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuParameterId;

    /**
     * @var int
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;

    /**
     * @var EcuParameterSets
     *
     * @ORM\ManyToOne(targetEntity="EcuParameterSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameter_set_id", referencedColumnName="ecu_parameter_set_id")
     * })
     */
    private $ecuParameterSet;

    /**
     * @var Ecus
     *
     * @ORM\ManyToOne(targetEntity="Ecus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ecu_id")
     * })
     */
    private $ecu;

    public function getEcuParameterId(): ?int
    {
        return $this->ecuParameterId;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getEcuParameterSet(): ?EcuParameterSets
    {
        return $this->ecuParameterSet;
    }

    public function setEcuParameterSet(?EcuParameterSets $ecuParameterSet): self
    {
        $this->ecuParameterSet = $ecuParameterSet;

        return $this;
    }

    public function getEcu(): ?Ecus
    {
        return $this->ecu;
    }

    public function setEcu(?Ecus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }


}
