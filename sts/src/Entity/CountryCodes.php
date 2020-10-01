<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CountryCodes
 *
 * @ORM\Table(name="country_codes")
 * @ORM\Entity
 */
class CountryCodes
{
    /**
     * @var int
     *
     * @ORM\Column(name="country_code_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="country_codes_country_code_id_seq", allocationSize=1, initialValue=1)
     */
    private $countryCodeId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private $country;

    public function getCountryCodeId(): ?int
    {
        return $this->countryCodeId;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }


}
