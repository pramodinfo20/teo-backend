<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Components
 *
 * @ORM\Table(name="components")
 * @ORM\Entity
 */
class Components
{
    /**
     * @var string
     *
     * @ORM\Column(name="part_number", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="components_part_number_seq", allocationSize=1, initialValue=1)
     */
    private $partNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="part_name", type="text", nullable=true)
     */
    private $partName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="element_name", type="text", nullable=true)
     */
    private $elementName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="version", type="text", nullable=true)
     */
    private $version;

    /**
     * @var string|null
     *
     * @ORM\Column(name="context", type="text", nullable=true)
     */
    private $context;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lifecyclestatus", type="text", nullable=true)
     */
    private $lifecyclestatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="unit", type="text", nullable=true)
     */
    private $unit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="objecttype", type="text", nullable=true)
     */
    private $objecttype;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="ebom_datum", type="datetimetz", nullable=true)
     */
    private $ebomDatum;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="phantomfertigungsteil", type="boolean", nullable=true)
     */
    private $phantomfertigungsteil;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="workstep", type="boolean", nullable=true)
     */
    private $workstep;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sml", type="text", nullable=true)
     */
    private $sml;

    public function getPartNumber(): ?string
    {
        return $this->partNumber;
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

    public function getElementName(): ?string
    {
        return $this->elementName;
    }

    public function setElementName(?string $elementName): self
    {
        $this->elementName = $elementName;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getLifecyclestatus(): ?string
    {
        return $this->lifecyclestatus;
    }

    public function setLifecyclestatus(?string $lifecyclestatus): self
    {
        $this->lifecyclestatus = $lifecyclestatus;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getObjecttype(): ?string
    {
        return $this->objecttype;
    }

    public function setObjecttype(?string $objecttype): self
    {
        $this->objecttype = $objecttype;

        return $this;
    }

    public function getEbomDatum(): ?\DateTimeInterface
    {
        return $this->ebomDatum;
    }

    public function setEbomDatum(?\DateTimeInterface $ebomDatum): self
    {
        $this->ebomDatum = $ebomDatum;

        return $this;
    }

    public function getPhantomfertigungsteil(): ?bool
    {
        return $this->phantomfertigungsteil;
    }

    public function setPhantomfertigungsteil(?bool $phantomfertigungsteil): self
    {
        $this->phantomfertigungsteil = $phantomfertigungsteil;

        return $this;
    }

    public function getWorkstep(): ?bool
    {
        return $this->workstep;
    }

    public function setWorkstep(?bool $workstep): self
    {
        $this->workstep = $workstep;

        return $this;
    }

    public function getSml(): ?string
    {
        return $this->sml;
    }

    public function setSml(?string $sml): self
    {
        $this->sml = $sml;

        return $this;
    }


}
