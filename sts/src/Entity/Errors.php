<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Errors
 *
 * @ORM\Table(name="errors", indexes={@ORM\Index(name="errors_depot_id_idx", columns={"depot_id"}),
 *                           @ORM\Index(name="errors_vehicle_id_idx", columns={"vehicle_id"}),
 *                                                                    @ORM\Index(name="errors_description_idx", columns={"description"}),
 *                                                                                                              @ORM\Index(name="errors_is_depot_error_idx", columns={"is_depot_error"}),
 *                                                                                                                                                           @ORM\Index(name="IDX_3C51531D41859289", columns={"division_id"})})
 * @ORM\Entity
 */
class Errors
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetimetz", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestamp;

    /**
     * @var int
     *
     * @ORM\Column(name="type_id", type="integer", nullable=false, options={"default"="-1"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typeId = '-1';

    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $c2cbox;

    /**
     * @var string
     *
     * @ORM\Column(name="severity_code", type="string", nullable=false)
     */
    private $severityCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="append1", type="text", nullable=true)
     */
    private $append1;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_depot_error", type="boolean", nullable=false)
     */
    private $isDepotError = false;

    /**
     * @var Divisions
     *
     * @ORM\ManyToOne(targetEntity="Divisions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="division_id", referencedColumnName="division_id")
     * })
     */
    private $division;

    /**
     * @var Vehicles
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicle;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function getSeverityCode(): ?string
    {
        return $this->severityCode;
    }

    public function setSeverityCode(string $severityCode): self
    {
        $this->severityCode = $severityCode;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAppend1(): ?string
    {
        return $this->append1;
    }

    public function setAppend1(?string $append1): self
    {
        $this->append1 = $append1;

        return $this;
    }

    public function getIsDepotError(): ?bool
    {
        return $this->isDepotError;
    }

    public function setIsDepotError(bool $isDepotError): self
    {
        $this->isDepotError = $isDepotError;

        return $this;
    }

    public function getDivision(): ?Divisions
    {
        return $this->division;
    }

    public function setDivision(?Divisions $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getVehicle(): ?Vehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getDepot(): ?Depots
    {
        return $this->depot;
    }

    public function setDepot(?Depots $depot): self
    {
        $this->depot = $depot;

        return $this;
    }


}
