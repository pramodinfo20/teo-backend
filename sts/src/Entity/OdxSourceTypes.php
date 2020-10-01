<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OdxSourceTypes
 *
 * @ORM\Table(name="odx_source_types", uniqueConstraints={@ORM\UniqueConstraint(name="odx_source_types_odx_source_type_name_key", columns={"odx_source_type_name"})})
 * @ORM\Entity
 */
class OdxSourceTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="odx_source_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="odx_source_types_odx_source_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $odxSourceTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="odx_source_type_name", type="text", nullable=false)
     */
    private $odxSourceTypeName;

    public function getOdxSourceTypeId(): ?int
    {
        return $this->odxSourceTypeId;
    }

    public function getOdxSourceTypeName(): ?string
    {
        return $this->odxSourceTypeName;
    }

    public function setOdxSourceTypeName(string $odxSourceTypeName): self
    {
        $this->odxSourceTypeName = $odxSourceTypeName;

        return $this;
    }

    public function __toString(): ?string
    {
        return $this->odxSourceTypeName;
    }


}
