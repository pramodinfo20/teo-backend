<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepotTypes
 *
 * @ORM\Table(name="depot_types")
 * @ORM\Entity
 */
class DepotTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="depot_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="depot_types_depot_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $depotTypeId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="depot_type", type="text", nullable=true)
     */
    private $depotType;

    public function getDepotTypeId(): ?int
    {
        return $this->depotTypeId;
    }

    public function getDepotType(): ?string
    {
        return $this->depotType;
    }

    public function setDepotType(?string $depotType): self
    {
        $this->depotType = $depotType;

        return $this;
    }


}
