<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EbomParts
 *
 * @ORM\Table(name="ebom_parts", uniqueConstraints={@ORM\UniqueConstraint(name="ebom_parts_ebom_part_name_key", columns={"ebom_part_name"}), @ORM\UniqueConstraint(name="ebom_parts_sts_part_number_key", columns={"sts_part_number"})})
 * @ORM\Entity
 */
class EbomParts
{
    /**
     * @var int
     *
     * @ORM\Column(name="ebom_part_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ebom_parts_ebom_part_id_seq", allocationSize=1, initialValue=1)
     */
    private $ebomPartId;

    /**
     * @var string
     *
     * @ORM\Column(name="ebom_part_name", type="text", nullable=false)
     */
    private $ebomPartName;

    /**
     * @var string
     *
     * @ORM\Column(name="sts_part_number", type="text", nullable=false)
     */
    private $stsPartNumber;

    public function getEbomPartId(): ?int
    {
        return $this->ebomPartId;
    }

    public function getEbomPartName(): ?string
    {
        return $this->ebomPartName;
    }

    public function setEbomPartName(string $ebomPartName): self
    {
        $this->ebomPartName = $ebomPartName;

        return $this;
    }

    public function getStsPartNumber(): ?string
    {
        return $this->stsPartNumber;
    }

    public function setStsPartNumber(string $stsPartNumber): self
    {
        $this->stsPartNumber = $stsPartNumber;

        return $this;
    }


}
