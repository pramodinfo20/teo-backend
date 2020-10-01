<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workshops
 *
 * @ORM\Table(name="workshops", indexes={@ORM\Index(name="IDX_879CA6A0106583B3", columns={"workshop_company_id"})})
 * @ORM\Entity
 */
class Workshops
{
    /**
     * @var int
     *
     * @ORM\Column(name="workshop_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="workshops_workshop_id_seq", allocationSize=1, initialValue=1)
     */
    private $workshopId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zip_code", type="text", nullable=true)
     */
    private $zipCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="location", type="text", nullable=true)
     */
    private $location;

    /**
     * @var string|null
     *
     * @ORM\Column(name="street", type="text", nullable=true)
     */
    private $street;

    /**
     * @var int|null
     *
     * @ORM\Column(name="house_number", type="integer", nullable=true)
     */
    private $houseNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="abbreviation", type="text", nullable=true)
     */
    private $abbreviation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="salutatory_address", type="text", nullable=true)
     */
    private $salutatoryAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="text", nullable=true)
     */
    private $phoneNumber;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="contract_date", type="date", nullable=true)
     */
    private $contractDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="accounts_payable_number", type="bigint", nullable=true)
     */
    private $accountsPayableNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="person1", type="text", nullable=true)
     */
    private $person1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number_person1", type="text", nullable=true)
     */
    private $phoneNumberPerson1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email_person1", type="string", length=254, nullable=true)
     */
    private $emailPerson1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="text", nullable=true)
     */
    private $country;

    /**
     * @var WorkshopCompanies
     *
     * @ORM\ManyToOne(targetEntity="WorkshopCompanies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workshop_company_id", referencedColumnName="workshop_company_id")
     * })
     */
    private $workshopCompany;

    public function getWorkshopId(): ?int
    {
        return $this->workshopId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber(): ?int
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?int $houseNumber): self
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getSalutatoryAddress(): ?string
    {
        return $this->salutatoryAddress;
    }

    public function setSalutatoryAddress(?string $salutatoryAddress): self
    {
        $this->salutatoryAddress = $salutatoryAddress;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getContractDate(): ?\DateTimeInterface
    {
        return $this->contractDate;
    }

    public function setContractDate(?\DateTimeInterface $contractDate): self
    {
        $this->contractDate = $contractDate;

        return $this;
    }

    public function getAccountsPayableNumber(): ?int
    {
        return $this->accountsPayableNumber;
    }

    public function setAccountsPayableNumber(?int $accountsPayableNumber): self
    {
        $this->accountsPayableNumber = $accountsPayableNumber;

        return $this;
    }

    public function getPerson1(): ?string
    {
        return $this->person1;
    }

    public function setPerson1(?string $person1): self
    {
        $this->person1 = $person1;

        return $this;
    }

    public function getPhoneNumberPerson1(): ?string
    {
        return $this->phoneNumberPerson1;
    }

    public function setPhoneNumberPerson1(?string $phoneNumberPerson1): self
    {
        $this->phoneNumberPerson1 = $phoneNumberPerson1;

        return $this;
    }

    public function getEmailPerson1(): ?string
    {
        return $this->emailPerson1;
    }

    public function setEmailPerson1(?string $emailPerson1): self
    {
        $this->emailPerson1 = $emailPerson1;

        return $this;
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

    public function getWorkshopCompany(): ?WorkshopCompanies
    {
        return $this->workshopCompany;
    }

    public function setWorkshopCompany(?WorkshopCompanies $workshopCompany): self
    {
        $this->workshopCompany = $workshopCompany;

        return $this;
    }


}
