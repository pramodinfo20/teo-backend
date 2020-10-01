<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleAttributeValues
 *
 * @ORM\Table(name="vehicle_attribute_values")
 * @ORM\Entity
 */
class VehicleAttributeValues
{
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
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $valueId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="default", type="boolean", nullable=true)
     */
    private $default = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="partnumber", type="text", nullable=true)
     */
    private $partnumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    public function getAttributeId(): ?int
    {
        return $this->attributeId;
    }

    public function getValueId(): ?int
    {
        return $this->valueId;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

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

    public function getDefault(): ?bool
    {
        return $this->default;
    }

    public function setDefault(?bool $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function getPartnumber(): ?string
    {
        return $this->partnumber;
    }

    public function setPartnumber(?string $partnumber): self
    {
        $this->partnumber = $partnumber;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }


}
