<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XmlAttributes
 *
 * @ORM\Table(name="xml_attributes", indexes={@ORM\Index(name="IDX_555D90F97AB8D155",
 *                                   columns={"ecu_parameter_set_id"})})
 * @ORM\Entity
 */
class XmlAttributes
{
    /**
     * @var int
     *
     * @ORM\Column(name="xml_attribute_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="xml_attributes_xml_attribute_id_seq", allocationSize=1, initialValue=1)
     */
    private $xmlAttributeId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="odx_attribute_name", type="text", nullable=true)
     */
    private $odxAttributeName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="odx_attribute_value", type="text", nullable=true)
     */
    private $odxAttributeValue;

    /**
     * @var EcuParameterSets
     *
     * @ORM\ManyToOne(targetEntity="EcuParameterSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameter_set_id", referencedColumnName="ecu_parameter_set_id")
     * })
     */
    private $ecuParameterSet;

    public function getXmlAttributeId(): ?int
    {
        return $this->xmlAttributeId;
    }

    public function getOdxAttributeName(): ?string
    {
        return $this->odxAttributeName;
    }

    public function setOdxAttributeName(?string $odxAttributeName): self
    {
        $this->odxAttributeName = $odxAttributeName;

        return $this;
    }

    public function getOdxAttributeValue(): ?string
    {
        return $this->odxAttributeValue;
    }

    public function setOdxAttributeValue(?string $odxAttributeValue): self
    {
        $this->odxAttributeValue = $odxAttributeValue;

        return $this;
    }

    public function getEcuParameterSet(): ?EcuParameterSets
    {
        return $this->ecuParameterSet;
    }

    public function setEcuParameterSet(?EcuParameterSets $ecuParameterSet): self
    {
        $this->ecuParameterSet = $ecuParameterSet;

        return $this;
    }


}
