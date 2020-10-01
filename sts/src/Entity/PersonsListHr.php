<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonsListHr
 *
 * @ORM\Table(name="persons_list_hr", indexes={@ORM\Index(name="IDX_54AAA766CCCFBA31", columns={"upload_id"}), @ORM\Index(name="IDX_54AAA766B5BF0DDB", columns={"deputy_organization_id"}), @ORM\Index(name="IDX_54AAA76632C8A3DE", columns={"organization_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PersonsListHrRepository")
 */
class PersonsListHr
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="persons_list_hr_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="person", type="text", nullable=false)
     */
    private $person;

    /**
     * @var string
     *
     * @ORM\Column(name="business_unit", type="text", nullable=false)
     */
    private $businessUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="kind", type="text", nullable=false)
     */
    private $kind;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_leader", type="boolean", nullable=true)
     */
    private $isLeader = false;

    /**
     * @var HistoryPersonsListHr
     *
     * @ORM\ManyToOne(targetEntity="HistoryPersonsListHr")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="upload_id", referencedColumnName="hplh_id")
     * })
     */
    private $upload;

    /**
     * @var StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deputy_organization_id", referencedColumnName="id")
     * })
     */
    private $deputyOrganization;

    /**
     * @var StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organization_id", referencedColumnName="id")
     * })
     */
    private $organization;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?string
    {
        return $this->person;
    }

    public function setPerson(string $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getBusinessUnit(): ?string
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(string $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    public function getKind(): ?string
    {
        return $this->kind;
    }

    public function setKind(string $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getIsLeader(): ?bool
    {
        return $this->isLeader;
    }

    public function setIsLeader(?bool $isLeader): self
    {
        $this->isLeader = $isLeader;

        return $this;
    }

    public function getUpload(): ?HistoryPersonsListHr
    {
        return $this->upload;
    }

    public function setUpload(?HistoryPersonsListHr $upload): self
    {
        $this->upload = $upload;

        return $this;
    }

    public function getDeputyOrganization(): ?StsOrganizationStructure
    {
        return $this->deputyOrganization;
    }

    public function setDeputyOrganization(?StsOrganizationStructure $deputyOrganization): self
    {
        $this->deputyOrganization = $deputyOrganization;

        return $this;
    }

    public function getOrganization(): ?StsOrganizationStructure
    {
        return $this->organization;
    }

    public function setOrganization(?StsOrganizationStructure $organization): self
    {
        $this->organization = $organization;

        return $this;
    }


}
