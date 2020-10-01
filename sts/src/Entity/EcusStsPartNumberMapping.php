<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcusStsPartNumberMapping
 *
 * @ORM\Table(name="ecus_sts_part_number_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecus_sts_part_number_mapping_ce_ecu_id_ebom_part_id_key", columns={"ce_ecu_id", "ebom_part_id"})}, indexes={@ORM\Index(name="IDX_4298459413D5BE78", columns={"ebom_part_id"}), @ORM\Index(name="IDX_429845948D3B41B6", columns={"ce_ecu_id"})})
 * @ORM\Entity
 */
class EcusStsPartNumberMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="espnm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecus_sts_part_number_mapping_espnm_id_seq", allocationSize=1,
     *                                                                                  initialValue=1)
     */
    private $espnmId;

    /**
     * @var EbomParts
     *
     * @ORM\ManyToOne(targetEntity="EbomParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_part_id", referencedColumnName="ebom_part_id")
     * })
     */
    private $ebomPart;

    /**
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ce_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ceEcu;

    public function getEspnmId(): ?int
    {
        return $this->espnmId;
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

    public function getCeEcu(): ?ConfigurationEcus
    {
        return $this->ceEcu;
    }

    public function setCeEcu(?ConfigurationEcus $ceEcu): self
    {
        $this->ceEcu = $ceEcu;

        return $this;
    }


}
