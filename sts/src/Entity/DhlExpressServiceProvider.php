<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * DhlExpressServiceProvider
 *
 * @ORM\Table(name="dhl_express_service_provider", indexes={@ORM\Index(name="IDX_D2FABEA740EBF7E2", columns={"dhl_express_division_id"})})
 * @ORM\Entity
 */
class DhlExpressServiceProvider
{
    /**
     * @var int
     *
     * @ORM\Column(name="service_provider_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dhl_express_service_provider_service_provider_id_seq", allocationSize=1,
     *                                                                                             initialValue=1)
     */
    private $serviceProviderId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var DhlExpressDivisions
     *
     * @ORM\ManyToOne(targetEntity="DhlExpressDivisions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dhl_express_division_id", referencedColumnName="dhl_express_division_id")
     * })
     */
    private $dhlExpressDivision;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Vehicles", mappedBy="serviceProvider")
     */
    private $vehicle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicle = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getServiceProviderId(): ?int
    {
        return $this->serviceProviderId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDhlExpressDivision(): ?DhlExpressDivisions
    {
        return $this->dhlExpressDivision;
    }

    public function setDhlExpressDivision(?DhlExpressDivisions $dhlExpressDivision): self
    {
        $this->dhlExpressDivision = $dhlExpressDivision;

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
            $vehicle->addServiceProvider($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicles $vehicle): self
    {
        if ($this->vehicle->contains($vehicle)) {
            $this->vehicle->removeElement($vehicle);
            $vehicle->removeServiceProvider($this);
        }

        return $this;
    }

}
