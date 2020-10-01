<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductionProtocolParts
 *
 * @ORM\Table(name="production_protocol_parts")
 * @ORM\Entity
 */
class ProductionProtocolParts
{
    /**
     * @var int
     *
     * @ORM\Column(name="production_part_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="production_protocol_parts_production_part_id_seq", allocationSize=1,
     *                                                                                         initialValue=1)
     */
    private $productionPartId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="part_name", type="text", nullable=true)
     */
    private $partName;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_dp", type="boolean", nullable=true)
     */
    private $isDp = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="related_part_id", type="integer", nullable=true)
     */
    private $relatedPartId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="available", type="boolean", nullable=true, options={"default"="1"})
     */
    private $available = true;

    public function getProductionPartId(): ?int
    {
        return $this->productionPartId;
    }

    public function getPartName(): ?string
    {
        return $this->partName;
    }

    public function setPartName(?string $partName): self
    {
        $this->partName = $partName;

        return $this;
    }

    public function getIsDp(): ?bool
    {
        return $this->isDp;
    }

    public function setIsDp(?bool $isDp): self
    {
        $this->isDp = $isDp;

        return $this;
    }

    public function getRelatedPartId(): ?int
    {
        return $this->relatedPartId;
    }

    public function setRelatedPartId(?int $relatedPartId): self
    {
        $this->relatedPartId = $relatedPartId;

        return $this;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(?bool $available): self
    {
        $this->available = $available;

        return $this;
    }


}
