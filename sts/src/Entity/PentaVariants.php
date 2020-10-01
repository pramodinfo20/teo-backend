<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PentaVariants
 *
 * @ORM\Table(name="penta_variants", uniqueConstraints={@ORM\UniqueConstraint(name="penta_variants_configuration_color_id_penta_variant_name_key", columns={"configuration_color_id", "penta_variant_name"}), @ORM\UniqueConstraint(name="penta_variants_configuration_color_id_sub_vehicle_configura_key", columns={"configuration_color_id", "sub_vehicle_configuration_id"})}, indexes={@ORM\Index(name="IDX_E2E450C9602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_E2E450C91B3FA8AE", columns={"configuration_color_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PentaVariantsRepository")
 */
class PentaVariants
{
    /**
     * @var int
     *
     * @ORM\Column(name="penta_variant_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="penta_variants_penta_variant_id_seq", allocationSize=1, initialValue=1)
     */
    private $pentaVariantId;

    /**
     * @var string
     *
     * @ORM\Column(name="penta_variant_name", type="text", nullable=false)
     */
    private $pentaVariantName;

    /**
     * @var PentaNumbers
     *
     * @ORM\ManyToOne(targetEntity="PentaNumbers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="old_penta_number_id", referencedColumnName="penta_number_id")
     * })
     */
    private $oldPentaNumber;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $subVehicleConfiguration;

    /**
     * @var ConfigurationColors
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationColors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="configuration_color_id", referencedColumnName="configuration_color_id")
     * })
     */
    private $configurationColor;

    public function getPentaVariantId(): ?int
    {
        return $this->pentaVariantId;
    }

    public function getPentaVariantName(): ?string
    {
        return $this->pentaVariantName;
    }

    public function setPentaVariantName(string $pentaVariantName): self
    {
        $this->pentaVariantName = $pentaVariantName;

        return $this;
    }

    public function getOldPentaNumber(): ?PentaNumbers
    {
        return $this->oldPentaNumber;
    }

    public function setOldPentaNumber(?PentaNumbers $oldPentaNumber): self
    {
        $this->oldPentaNumber = $oldPentaNumber;

        return $this;
    }

    public function getSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->subVehicleConfiguration;
    }

    public function setSubVehicleConfiguration(?SubVehicleConfigurations $subVehicleConfiguration): self
    {
        $this->subVehicleConfiguration = $subVehicleConfiguration;

        return $this;
    }

    public function getConfigurationColor(): ?ConfigurationColors
    {
        return $this->configurationColor;
    }

    public function setConfigurationColor(?ConfigurationColors $configurationColor): self
    {
        $this->configurationColor = $configurationColor;

        return $this;
    }


}
