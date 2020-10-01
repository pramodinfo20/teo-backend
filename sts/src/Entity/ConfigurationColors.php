<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigurationColors
 *
 * @ORM\Table(name="configuration_colors", uniqueConstraints={@ORM\UniqueConstraint(name="configuration_colors_configuration_color_name_key", columns={"configuration_color_name"})}, indexes={@ORM\Index(name="IDX_3499E402E95CFEF", columns={"allowed_symbols_id"})})
 * @ORM\Entity
 */
class ConfigurationColors
{
    /**
     * @var int
     *
     * @ORM\Column(name="configuration_color_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="configuration_colors_configuration_color_id_seq", allocationSize=1, initialValue=1)
     */
    private $configurationColorId;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration_color_name", type="text", nullable=false)
     */
    private $configurationColorName;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration_color_key", type="text", nullable=false)
     */
    private $configurationColorKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_color_code", type="text", nullable=true)
     */
    private $vinColorCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rgb", type="text", nullable=true)
     */
    private $rgb;

    /**
     * @var \AllowedSymbols
     *
     * @ORM\ManyToOne(targetEntity="AllowedSymbols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="allowed_symbols_id", referencedColumnName="allowed_symbols_id")
     * })
     */
    private $allowedSymbols;

    public function getConfigurationColorId(): ?int
    {
        return $this->configurationColorId;
    }

    public function getConfigurationColorName(): ?string
    {
        return $this->configurationColorName;
    }

    public function setConfigurationColorName(string $configurationColorName): self
    {
        $this->configurationColorName = $configurationColorName;

        return $this;
    }

    public function getConfigurationColorKey(): ?string
    {
        return $this->configurationColorKey;
    }

    public function setConfigurationColorKey(string $configurationColorKey): self
    {
        $this->configurationColorKey = $configurationColorKey;

        return $this;
    }

    public function getVinColorCode(): ?string
    {
        return $this->vinColorCode;
    }

    public function setVinColorCode(?string $vinColorCode): self
    {
        $this->vinColorCode = $vinColorCode;

        return $this;
    }

    public function getRgb(): ?string
    {
        return $this->rgb;
    }

    public function setRgb(?string $rgb): self
    {
        $this->rgb = $rgb;

        return $this;
    }

    public function getAllowedSymbols(): ?AllowedSymbols
    {
        return $this->allowedSymbols;
    }

    public function setAllowedSymbols(?AllowedSymbols $allowedSymbols): self
    {
        $this->allowedSymbols = $allowedSymbols;

        return $this;
    }


}
