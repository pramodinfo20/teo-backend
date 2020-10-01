<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChargecompletedDescription
 *
 * @ORM\Table(name="chargecompleted_description")
 * @ORM\Entity
 */
class ChargecompletedDescription
{
    /**
     * @var int
     *
     * @ORM\Column(name="bcm_c2c_chargecompleted", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="chargecompleted_description_bcm_c2c_chargecompleted_seq", allocationSize=1,
     *                                                                                                initialValue=1)
     */
    private $bcmC2cChargecompleted;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    public function getBcmC2cChargecompleted(): ?int
    {
        return $this->bcmC2cChargecompleted;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


}
