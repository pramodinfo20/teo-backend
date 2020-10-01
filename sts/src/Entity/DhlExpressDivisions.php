<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DhlExpressDivisions
 *
 * @ORM\Table(name="dhl_express_divisions")
 * @ORM\Entity
 */
class DhlExpressDivisions
{
    /**
     * @var int
     *
     * @ORM\Column(name="dhl_express_division_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dhl_express_divisions_dhl_express_division_id_seq", allocationSize=1,
     *                                                                                          initialValue=1)
     */
    private $dhlExpressDivisionId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=1000, nullable=false, options={"fixed"=true})
     */
    private $name;

    public function getDhlExpressDivisionId(): ?int
    {
        return $this->dhlExpressDivisionId;
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


}
