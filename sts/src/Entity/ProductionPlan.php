<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductionPlan
 *
 * @ORM\Table(name="production_plan")
 * @ORM\Entity
 */
class ProductionPlan
{
    /**
     * @var int
     *
     * @ORM\Column(name="production_plan_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="production_plan_production_plan_id_seq", allocationSize=1, initialValue=1)
     */
    private $productionPlanId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="variant_value", type="integer", nullable=true)
     */
    private $variantValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="added_timestamp", type="datetimetz", nullable=true)
     */
    private $addedTimestamp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="production_quantity", type="integer", nullable=true)
     */
    private $productionQuantity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="production_week", type="text", nullable=true)
     */
    private $productionWeek;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicles_produced", type="integer", nullable=true)
     */
    private $vehiclesProduced;

    /**
     * @var string|null
     *
     * @ORM\Column(name="update_timestamp", type="text", nullable=true)
     */
    private $updateTimestamp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="production_to_pool_qty", type="integer", nullable=true)
     */
    private $productionToPoolQty = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="production_year", type="integer", nullable=true, options={"default"="2016"})
     */
    private $productionYear = '2016';

    public function getProductionPlanId(): ?int
    {
        return $this->productionPlanId;
    }

    public function getVariantValue(): ?int
    {
        return $this->variantValue;
    }

    public function setVariantValue(?int $variantValue): self
    {
        $this->variantValue = $variantValue;

        return $this;
    }

    public function getAddedTimestamp(): ?\DateTimeInterface
    {
        return $this->addedTimestamp;
    }

    public function setAddedTimestamp(?\DateTimeInterface $addedTimestamp): self
    {
        $this->addedTimestamp = $addedTimestamp;

        return $this;
    }

    public function getProductionQuantity(): ?int
    {
        return $this->productionQuantity;
    }

    public function setProductionQuantity(?int $productionQuantity): self
    {
        $this->productionQuantity = $productionQuantity;

        return $this;
    }

    public function getProductionWeek(): ?string
    {
        return $this->productionWeek;
    }

    public function setProductionWeek(?string $productionWeek): self
    {
        $this->productionWeek = $productionWeek;

        return $this;
    }

    public function getVehiclesProduced(): ?int
    {
        return $this->vehiclesProduced;
    }

    public function setVehiclesProduced(?int $vehiclesProduced): self
    {
        $this->vehiclesProduced = $vehiclesProduced;

        return $this;
    }

    public function getUpdateTimestamp(): ?string
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?string $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }

    public function getProductionToPoolQty(): ?int
    {
        return $this->productionToPoolQty;
    }

    public function setProductionToPoolQty(?int $productionToPoolQty): self
    {
        $this->productionToPoolQty = $productionToPoolQty;

        return $this;
    }

    public function getProductionYear(): ?int
    {
        return $this->productionYear;
    }

    public function setProductionYear(?int $productionYear): self
    {
        $this->productionYear = $productionYear;

        return $this;
    }


}
