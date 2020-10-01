<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Analysis.availabilityParameters
 *
 * @ORM\Table(name="availability_parameters", schema="analysis")
 * @ORM\Entity
 */
class AvailabilityParameters
{
    /**
     * @var int
     *
     * @ORM\Column(name="parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="availability_parameters_parameter_id_seq", allocationSize=1, initialValue=1)
     */
    private $parameterId;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter", type="text", nullable=false)
     */
    private $parameter;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=10, scale=0, nullable=false)
     */
    private $value;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Vehicles", mappedBy="parameter")
     */
    private $vehicle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicle = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getParameterId(): ?int
    {
        return $this->parameterId;
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    public function setParameter(string $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Collection|Vehicles[]
     */
    public function getVehicle(): Collection
    {
        return $this->vehicle;
    }

    public function addVehicle(Vehicles $vehicle): self
    {
        if (!$this->vehicle->contains($vehicle)) {
            $this->vehicle[] = $vehicle;
            $vehicle->addParameter($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicles $vehicle): self
    {
        if ($this->vehicle->contains($vehicle)) {
            $this->vehicle->removeElement($vehicle);
            $vehicle->removeParameter($this);
        }

        return $this;
    }

}
