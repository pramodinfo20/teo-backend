<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuTagConfiguration
 *
 * @ORM\Table(name="ecu_tag_configuration", indexes={@ORM\Index(name="IDX_891510A1C99F95F4",
 *                                          columns={"ecu_revision_id"})})
 * @ORM\Entity
 */
class EcuTagConfiguration
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetimetz", nullable=false, options={"default"="now()"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestamp = 'now()';

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $tag;

    /**
     * @var int
     *
     * @ORM\Column(name="ecu_parameter_set_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ecuParameterSetId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tag_value", type="text", nullable=true)
     */
    private $tagValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="fill_tag_value", type="boolean", nullable=false)
     */
    private $fillTagValue = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_odx_tag", type="boolean", nullable=false, options={"default"="1"})
     */
    private $isOdxTag = true;

    /**
     * @var EcuRevisions
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EcuRevisions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_revision_id", referencedColumnName="ecu_revision_id")
     * })
     */
    private $ecuRevision;

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getEcuParameterSetId(): ?int
    {
        return $this->ecuParameterSetId;
    }

    public function getTagValue(): ?string
    {
        return $this->tagValue;
    }

    public function setTagValue(?string $tagValue): self
    {
        $this->tagValue = $tagValue;

        return $this;
    }

    public function getFillTagValue(): ?bool
    {
        return $this->fillTagValue;
    }

    public function setFillTagValue(bool $fillTagValue): self
    {
        $this->fillTagValue = $fillTagValue;

        return $this;
    }

    public function getIsOdxTag(): ?bool
    {
        return $this->isOdxTag;
    }

    public function setIsOdxTag(bool $isOdxTag): self
    {
        $this->isOdxTag = $isOdxTag;

        return $this;
    }

    public function getEcuRevision(): ?EcuRevisions
    {
        return $this->ecuRevision;
    }

    public function setEcuRevision(?EcuRevisions $ecuRevision): self
    {
        $this->ecuRevision = $ecuRevision;

        return $this;
    }


}
