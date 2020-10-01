<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldVehicleConfiguration
 *
 * @ORM\Table(name="old_vehicle_configuration")
 * @ORM\Entity
 */
class OldVehicleConfiguration
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleId;

    /**
     * @var int
     *
     * @ORM\Column(name="timestamp", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestamp;

    /**
     * @var int
     *
     * @ORM\Column(name="attribute_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $attributeId;

    /**
     * @var int
     *
     * @ORM\Column(name="value_id", type="integer", nullable=false)
     */
    private $valueId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user", type="text", nullable=true)
     */
    private $user;

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function getAttributeId(): ?int
    {
        return $this->attributeId;
    }

    public function getValueId(): ?int
    {
        return $this->valueId;
    }

    public function setValueId(int $valueId): self
    {
        $this->valueId = $valueId;

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

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }


}
