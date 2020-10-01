<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwProperties
 *
 * @ORM\Table(name="ecu_sw_properties")
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwPropertiesRepository")
 */
class EcuSwProperties
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_property_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_properties_ecu_sw_property_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuSwPropertyId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    public function getEcuSwPropertyId(): ?int
    {
        return $this->ecuSwPropertyId;
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

}
