<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwPropertiesMapping
 *
 * @ORM\Table(name="ecu_sw_properties_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_properties_mapping_ecu_sw_version_id_ecu_sw_property_key", columns={"ecu_sw_version_id", "ecu_sw_property_id"})}, indexes={@ORM\Index(name="IDX_CE47A3561B08A8EF", columns={"ecu_sw_property_id"}), @ORM\Index(name="IDX_CE47A356EF576A6", columns={"ecu_sw_version_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwPropertiesMappingRepository")
 */
class EcuSwPropertiesMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_properties_mapping_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_properties_mapping_ecu_sw_properties_mapping_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuSwPropertiesMappingId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="property_order", type="integer", nullable=true)
     */
    private $propertyOrder;

    /**
     * @var EcuSwProperties
     *
     * @ORM\ManyToOne(targetEntity="EcuSwProperties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_property_id", referencedColumnName="ecu_sw_property_id")
     * })
     */
    private $ecuSwProperty;

    /**
     * @var EcuSwVersions
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $ecuSwVersion;

    public function getEcuSwPropertiesMappingId(): ?int
    {
        return $this->ecuSwPropertiesMappingId;
    }

    public function getPropertyOrder(): ?int
    {
        return $this->propertyOrder;
    }

    public function setPropertyOrder(?int $propertyOrder): self
    {
        $this->propertyOrder = $propertyOrder;

        return $this;
    }

    public function getEcuSwProperty(): ?EcuSwProperties
    {
        return $this->ecuSwProperty;
    }

    public function setEcuSwProperty(?EcuSwProperties $ecuSwProperty): self
    {
        $this->ecuSwProperty = $ecuSwProperty;

        return $this;
    }

    public function getEcuSwVersion(): ?EcuSwVersions
    {
        return $this->ecuSwVersion;
    }

    public function setEcuSwVersion(?EcuSwVersions $ecuSwVersion): self
    {
        $this->ecuSwVersion = $ecuSwVersion;

        return $this;
    }


}
