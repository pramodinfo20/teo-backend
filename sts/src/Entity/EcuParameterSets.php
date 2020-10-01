<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuParameterSets
 *
 * @ORM\Table(name="ecu_parameter_sets", indexes={@ORM\Index(name="IDX_D1431CC3F8BD700D", columns={"unit_id"}),
 *                                       @ORM\Index(name="IDX_D1431CC3C54C8C93", columns={"type_id"}),
 *                                                                               @ORM\Index(name="IDX_D1431CC3727ACA70", columns={"parent_id"})})
 * @ORM\Entity
 */
class EcuParameterSets
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_parameter_set_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_parameter_sets_ecu_parameter_set_id_seq", allocationSize=1,
     *                                                                                    initialValue=1)
     */
    private $ecuParameterSetId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="odx_name", type="text", nullable=true)
     */
    private $odxName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string|null
     *
     * @ORM\Column(name="odx_tag_name", type="text", nullable=true)
     */
    private $odxTagName;

    /**
     * @var bool
     *
     * @ORM\Column(name="write_value_to_tag", type="boolean", nullable=false)
     */
    private $writeValueToTag = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_in_old_format", type="boolean", nullable=false)
     */
    private $useInOldFormat = false;

    /**
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id")
     * })
     */
    private $unit;

    /**
     * @var ParameterValueTypes
     *
     * @ORM\ManyToOne(targetEntity="ParameterValueTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="parameter_value_types_id")
     * })
     */
    private $type;

    /**
     * @var EcuParameterSets
     *
     * @ORM\ManyToOne(targetEntity="EcuParameterSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="ecu_parameter_set_id")
     * })
     */
    private $parent;

    public function getEcuParameterSetId(): ?int
    {
        return $this->ecuParameterSetId;
    }

    public function getOdxName(): ?string
    {
        return $this->odxName;
    }

    public function setOdxName(?string $odxName): self
    {
        $this->odxName = $odxName;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getOdxTagName(): ?string
    {
        return $this->odxTagName;
    }

    public function setOdxTagName(?string $odxTagName): self
    {
        $this->odxTagName = $odxTagName;

        return $this;
    }

    public function getWriteValueToTag(): ?bool
    {
        return $this->writeValueToTag;
    }

    public function setWriteValueToTag(bool $writeValueToTag): self
    {
        $this->writeValueToTag = $writeValueToTag;

        return $this;
    }

    public function getUseInOldFormat(): ?bool
    {
        return $this->useInOldFormat;
    }

    public function setUseInOldFormat(bool $useInOldFormat): self
    {
        $this->useInOldFormat = $useInOldFormat;

        return $this;
    }

    public function getUnit(): ?Units
    {
        return $this->unit;
    }

    public function setUnit(?Units $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getType(): ?ParameterValueTypes
    {
        return $this->type;
    }

    public function setType(?ParameterValueTypes $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }


}
