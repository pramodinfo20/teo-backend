<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigurationCountryCodes
 *
 * @ORM\Table(name="configuration_country_codes", uniqueConstraints={@ORM\UniqueConstraint(name="configuration_country_codes_configuration_country_code_name_key", columns={"configuration_country_code_name"})})
 * @ORM\Entity
 */
class ConfigurationCountryCodes
{
    /**
     * @var int
     *
     * @ORM\Column(name="configuration_country_code_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="configuration_country_codes_configuration_country_code_id_seq", allocationSize=1,
     *                                                                                                      initialValue=1)
     */
    private $configurationCountryCodeId;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration_country_code_name", type="text", nullable=false)
     */
    private $configurationCountryCodeName;

    public function getConfigurationCountryCodeId(): ?int
    {
        return $this->configurationCountryCodeId;
    }

    public function getConfigurationCountryCodeName(): ?string
    {
        return $this->configurationCountryCodeName;
    }

    public function setConfigurationCountryCodeName(string $configurationCountryCodeName): self
    {
        $this->configurationCountryCodeName = $configurationCountryCodeName;

        return $this;
    }


}
