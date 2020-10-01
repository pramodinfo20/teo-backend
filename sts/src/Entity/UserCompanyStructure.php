<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserCompanyStructure
 *
 * @ORM\Table(name="user_company_structure", indexes={@ORM\Index(name="IDX_A40506312534008B", columns={"structure_id"}), @ORM\Index(name="IDX_A4050631A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class UserCompanyStructure
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="user_company_structure_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="is_leader", type="text", nullable=false, options={"default"="No"})
     */
    private $isLeader = 'No';

    /**
     * @var int|null
     *
     * @ORM\Column(name="is_deputy", type="integer", nullable=true)
     */
    private $isDeputy;

    /**
     * @var StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="structure_id", referencedColumnName="id")
     * })
     */
    private $structure;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsLeader(): ?string
    {
        return $this->isLeader;
    }

    public function setIsLeader(string $isLeader): self
    {
        $this->isLeader = $isLeader;

        return $this;
    }

    public function getIsDeputy(): ?int
    {
        return $this->isDeputy;
    }

    public function setIsDeputy(?int $isDeputy): self
    {
        $this->isDeputy = $isDeputy;

        return $this;
    }

    public function getStructure(): ?StsOrganizationStructure
    {
        return $this->structure;
    }

    public function setStructure(?StsOrganizationStructure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }


}
