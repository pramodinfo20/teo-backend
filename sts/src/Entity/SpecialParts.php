<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialParts
 *
 * @ORM\Table(name="special_parts", uniqueConstraints={@ORM\UniqueConstraint(name="special_parts_special_part_name_ebom_part_id_key", columns={"special_part_name", "ebom_part_id"})}, indexes={@ORM\Index(name="IDX_C57E09BF13D5BE78", columns={"ebom_part_id"})})
 * @ORM\Entity
 */
class SpecialParts
{
    /**
     * @var int
     *
     * @ORM\Column(name="special_part_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="special_parts_special_part_id_seq", allocationSize=1, initialValue=1)
     */
    private $specialPartId;

    /**
     * @var string
     *
     * @ORM\Column(name="special_part_name", type="text", nullable=false)
     */
    private $specialPartName;

    /**
     * @var EbomParts
     *
     * @ORM\ManyToOne(targetEntity="EbomParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_part_id", referencedColumnName="ebom_part_id")
     * })
     */
    private $ebomPart;

    public function getSpecialPartId(): ?int
    {
        return $this->specialPartId;
    }

    public function getSpecialPartName(): ?string
    {
        return $this->specialPartName;
    }

    public function setSpecialPartName(string $specialPartName): self
    {
        $this->specialPartName = $specialPartName;

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
