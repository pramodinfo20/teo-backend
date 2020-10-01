<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Divisions
 *
 * @ORM\Table(name="divisions", uniqueConstraints={@ORM\UniqueConstraint(name="divisions_dp_division_id_key", columns={"dp_division_id"})}, indexes={@ORM\Index(name="divisions_name_idx", columns={"name"})})
 * @ORM\Entity
 */
class Divisions
{
    /**
     * @var int
     *
     * @ORM\Column(name="division_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="divisions_division_id_seq", allocationSize=1, initialValue=1)
     */
    private $divisionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dp_division_id", type="integer", nullable=true)
     */
    private $dpDivisionId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="charging_control_enabled", type="boolean", nullable=true)
     */
    private $chargingControlEnabled = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost_center", type="text", nullable=true)
     */
    private $costCenter;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="production_location", type="boolean", nullable=false)
     */
    private $productionLocation = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="production_vin_key", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $productionVinKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost_center_1", type="string", length=8, nullable=true)
     */
    private $costCenter1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost_center_2", type="string", length=8, nullable=true)
     */
    private $costCenter2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost_center_3", type="string", length=8, nullable=true)
     */
    private $costCenter3;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost_center_4", type="string", length=8, nullable=true)
     */
    private $costCenter4;

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDpDivisionId(): ?int
    {
        return $this->dpDivisionId;
    }

    public function setDpDivisionId(?int $dpDivisionId): self
    {
        $this->dpDivisionId = $dpDivisionId;

        return $this;
    }

    public function getChargingControlEnabled(): ?bool
    {
        return $this->chargingControlEnabled;
    }

    public function setChargingControlEnabled(?bool $chargingControlEnabled): self
    {
        $this->chargingControlEnabled = $chargingControlEnabled;

        return $this;
    }

    public function getCostCenter(): ?string
    {
        return $this->costCenter;
    }

    public function setCostCenter(?string $costCenter): self
    {
        $this->costCenter = $costCenter;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getProductionLocation(): ?bool
    {
        return $this->productionLocation;
    }

    public function setProductionLocation(bool $productionLocation): self
    {
        $this->productionLocation = $productionLocation;

        return $this;
    }

    public function getProductionVinKey(): ?string
    {
        return $this->productionVinKey;
    }

    public function setProductionVinKey(?string $productionVinKey): self
    {
        $this->productionVinKey = $productionVinKey;

        return $this;
    }

    public function getCostCenter1(): ?string
    {
        return $this->costCenter1;
    }

    public function setCostCenter1(?string $costCenter1): self
    {
        $this->costCenter1 = $costCenter1;

        return $this;
    }

    public function getCostCenter2(): ?string
    {
        return $this->costCenter2;
    }

    public function setCostCenter2(?string $costCenter2): self
    {
        $this->costCenter2 = $costCenter2;

        return $this;
    }

    public function getCostCenter3(): ?string
    {
        return $this->costCenter3;
    }

    public function setCostCenter3(?string $costCenter3): self
    {
        $this->costCenter3 = $costCenter3;

        return $this;
    }

    public function getCostCenter4(): ?string
    {
        return $this->costCenter4;
    }

    public function setCostCenter4(?string $costCenter4): self
    {
        $this->costCenter4 = $costCenter4;

        return $this;
    }


}
