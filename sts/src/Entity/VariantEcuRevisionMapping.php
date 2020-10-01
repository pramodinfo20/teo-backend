<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VariantEcuRevisionMapping
 *
 * @ORM\Table(name="variant_ecu_revision_mapping", indexes={@ORM\Index(name="IDX_7F8EE9C53B69A9AF", columns={"variant_id"}), @ORM\Index(name="IDX_7F8EE9C571AE1636", columns={"rev_id"}), @ORM\Index(name="IDX_7F8EE9C534CA89DA", columns={"penta_id"}), @ORM\Index(name="IDX_7F8EE9C5F2887E5B", columns={"ecu_id"}), @ORM\Index(name="IDX_7F8EE9C57CB790D7", columns={"copy_from_penta_id"}), @ORM\Index(name="IDX_7F8EE9C5EAAC375F", columns={"copy_from_variant_id"})})
 * @ORM\Entity
 */
class VariantEcuRevisionMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="odx_download_mode", type="integer", nullable=false, options={"default"="1"})
     */
    private $odxDownloadMode = '1';

    /**
     * @var bool
     *
     * @ORM\Column(name="parameters_check_ok", type="boolean", nullable=false)
     */
    private $parametersCheckOk = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="parameters_released", type="boolean", nullable=false)
     */
    private $parametersReleased = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_last_change", type="datetimetz", nullable=true, options={"default"="now()"})
     */
    private $timestampLastChange = 'now()';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_copy", type="datetimetz", nullable=true)
     */
    private $timestampCopy;

    /**
     * @var bool
     *
     * @ORM\Column(name="ecu_used", type="boolean", nullable=false, options={"default"="1"})
     */
    private $ecuUsed = true;

    /**
     * @var VehicleVariants
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="variant_id", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $variant;

    /**
     * @var EcuRevisions
     *
     * @ORM\ManyToOne(targetEntity="EcuRevisions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rev_id", referencedColumnName="ecu_revision_id")
     * })
     */
    private $rev;

    /**
     * @var PentaNumbers
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PentaNumbers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="penta_id", referencedColumnName="penta_number_id")
     * })
     */
    private $penta;

    /**
     * @var Ecus
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Ecus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ecu_id")
     * })
     */
    private $ecu;

    /**
     * @var PentaNumbers
     *
     * @ORM\ManyToOne(targetEntity="PentaNumbers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="copy_from_penta_id", referencedColumnName="penta_number_id")
     * })
     */
    private $copyFromPenta;

    /**
     * @var VehicleVariants
     *
     * @ORM\ManyToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="copy_from_variant_id", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $copyFromVariant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOdxDownloadMode(): ?int
    {
        return $this->odxDownloadMode;
    }

    public function setOdxDownloadMode(int $odxDownloadMode): self
    {
        $this->odxDownloadMode = $odxDownloadMode;

        return $this;
    }

    public function getParametersCheckOk(): ?bool
    {
        return $this->parametersCheckOk;
    }

    public function setParametersCheckOk(bool $parametersCheckOk): self
    {
        $this->parametersCheckOk = $parametersCheckOk;

        return $this;
    }

    public function getParametersReleased(): ?bool
    {
        return $this->parametersReleased;
    }

    public function setParametersReleased(bool $parametersReleased): self
    {
        $this->parametersReleased = $parametersReleased;

        return $this;
    }

    public function getTimestampLastChange(): ?\DateTimeInterface
    {
        return $this->timestampLastChange;
    }

    public function setTimestampLastChange(?\DateTimeInterface $timestampLastChange): self
    {
        $this->timestampLastChange = $timestampLastChange;

        return $this;
    }

    public function getTimestampCopy(): ?\DateTimeInterface
    {
        return $this->timestampCopy;
    }

    public function setTimestampCopy(?\DateTimeInterface $timestampCopy): self
    {
        $this->timestampCopy = $timestampCopy;

        return $this;
    }

    public function getEcuUsed(): ?bool
    {
        return $this->ecuUsed;
    }

    public function setEcuUsed(bool $ecuUsed): self
    {
        $this->ecuUsed = $ecuUsed;

        return $this;
    }

    public function getVariant(): ?VehicleVariants
    {
        return $this->variant;
    }

    public function setVariant(?VehicleVariants $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getRev(): ?EcuRevisions
    {
        return $this->rev;
    }

    public function setRev(?EcuRevisions $rev): self
    {
        $this->rev = $rev;

        return $this;
    }

    public function getPenta(): ?PentaNumbers
    {
        return $this->penta;
    }

    public function setPenta(?PentaNumbers $penta): self
    {
        $this->penta = $penta;

        return $this;
    }

    public function getEcu(): ?Ecus
    {
        return $this->ecu;
    }

    public function setEcu(?Ecus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }

    public function getCopyFromPenta(): ?PentaNumbers
    {
        return $this->copyFromPenta;
    }

    public function setCopyFromPenta(?PentaNumbers $copyFromPenta): self
    {
        $this->copyFromPenta = $copyFromPenta;

        return $this;
    }

    public function getCopyFromVariant(): ?VehicleVariants
    {
        return $this->copyFromVariant;
    }

    public function setCopyFromVariant(?VehicleVariants $copyFromVariant): self
    {
        $this->copyFromVariant = $copyFromVariant;

        return $this;
    }


}
