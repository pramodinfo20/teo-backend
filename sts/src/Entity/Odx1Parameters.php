<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Odx1Parameters
 *
 * @ORM\Table(name="odx1_parameters")
 * @ORM\Entity
 */
class Odx1Parameters
{
    /**
     * @var bool
     *
     * @ORM\Column(name="is_also_odx2", type="boolean", nullable=false)
     */
    private $isAlsoOdx2;

    /**
     * @var int
     *
     * @ORM\Column(name="special_order_id_for_odx1", type="integer", nullable=false)
     */
    private $specialOrderIdForOdx1;

    /**
     * @var EcuSwParameters
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EcuSwParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="op_ecu_sw_parameter_id", referencedColumnName="ecu_sw_parameter_id")
     * })
     */
    private $opEcuSwParameter;

    public function getIsAlsoOdx2(): ?bool
    {
        return $this->isAlsoOdx2;
    }

    public function setIsAlsoOdx2(bool $isAlsoOdx2): self
    {
        $this->isAlsoOdx2 = $isAlsoOdx2;

        return $this;
    }

    public function getSpecialOrderIdForOdx1(): ?int
    {
        return $this->specialOrderIdForOdx1;
    }

    public function setSpecialOrderIdForOdx1(int $specialOrderIdForOdx1): self
    {
        $this->specialOrderIdForOdx1 = $specialOrderIdForOdx1;

        return $this;
    }

    public function getOpEcuSwParameter(): ?EcuSwParameters
    {
        return $this->opEcuSwParameter;
    }

    public function setOpEcuSwParameter(?EcuSwParameters $opEcuSwParameter): self
    {
        $this->opEcuSwParameter = $opEcuSwParameter;

        return $this;
    }


}
