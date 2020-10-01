<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldWcTypes
 *
 * @ORM\Table(name="old_wc_types")
 * @ORM\Entity
 */
class OldWcTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="wc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="old_wc_types_wc_id_seq", allocationSize=1, initialValue=1)
     */
    private $wcId;

    /**
     * @var string
     *
     * @ORM\Column(name="wc_name", type="text", nullable=false)
     */
    private $wcName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="KBA_name", type="text", nullable=true)
     */
    private $kbaName;

    public function getWcId(): ?int
    {
        return $this->wcId;
    }

    public function getWcName(): ?string
    {
        return $this->wcName;
    }

    public function setWcName(string $wcName): self
    {
        $this->wcName = $wcName;

        return $this;
    }

    public function getKbaName(): ?string
    {
        return $this->kbaName;
    }

    public function setKbaName(?string $kbaName): self
    {
        $this->kbaName = $kbaName;

        return $this;
    }


}
