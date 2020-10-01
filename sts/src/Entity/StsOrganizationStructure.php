<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StsOrganizationStructure
 *
 * @ORM\Table(name="sts_organization_structure", indexes={@ORM\Index(name="IDX_6697ADB2727ACA70", columns={"parent_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\StsOrganizationStructureRepository")
 */
class StsOrganizationStructure
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sts_organization_structure_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="costcenter", type="integer", nullable=true)
     */
    private $costcenter;

    /**
     * @var StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $parent;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCostcenter(): ?int
    {
        return $this->costcenter;
    }

    public function setCostcenter(?int $costcenter): self
    {
        $this->costcenter = $costcenter;

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
