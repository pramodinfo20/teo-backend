<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EbomAssemblies
 *
 * @ORM\Table(name="ebom_assemblies", indexes={@ORM\Index(name="IDX_353A2D22D8A8CA1", columns={"ebom_parent_parts"}),
 *                                    @ORM\Index(name="IDX_353A2D213D5BE78", columns={"ebom_part_id"})})
 * @ORM\Entity
 */
class EbomAssemblies
{
    /**
     * @var int
     *
     * @ORM\Column(name="ebom_assembly_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ebom_assemblies_ebom_assembly_id_seq", allocationSize=1, initialValue=1)
     */
    private $ebomAssemblyId;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var EbomParts
     *
     * @ORM\ManyToOne(targetEntity="EbomParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_parent_parts", referencedColumnName="ebom_part_id")
     * })
     */
    private $ebomParentParts;

    /**
     * @var EbomParts
     *
     * @ORM\ManyToOne(targetEntity="EbomParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_part_id", referencedColumnName="ebom_part_id")
     * })
     */
    private $ebomPart;

    public function getEbomAssemblyId(): ?int
    {
        return $this->ebomAssemblyId;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getEbomParentParts(): ?EbomParts
    {
        return $this->ebomParentParts;
    }

    public function setEbomParentParts(?EbomParts $ebomParentParts): self
    {
        $this->ebomParentParts = $ebomParentParts;

        return $this;
    }

    public function getEbomPart(): ?EbomParts
    {
        return $this->ebomPart;
    }

    public function setEbomPart(?EbomParts $ebomPart): self
    {
        $this->ebomPart = $ebomPart;

        return $this;
    }


}
