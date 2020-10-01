<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StsPrivilegs
 *
 * @ORM\Table(name="sts_privilegs", uniqueConstraints={@ORM\UniqueConstraint(name="sts_privileges_unique", columns={"user_id", "context", "ecu_id"})}, indexes={@ORM\Index(name="IDX_95243E0C3C506913", columns={"set_by_user"}), @ORM\Index(name="IDX_95243E0C7AB8D155", columns={"ecu_parameter_set_id"}), @ORM\Index(name="IDX_95243E0CA76ED395", columns={"user_id"}), @ORM\Index(name="IDX_95243E0CF2887E5B", columns={"ecu_id"})})
 * @ORM\Entity
 */
class StsPrivilegs
{
    /**
     * @var int
     *
     * @ORM\Column(name="sts_privileg_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sts_privilegs_sts_privileg_id_seq", allocationSize=1, initialValue=1)
     */
    private $stsPrivilegId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="context", type="text", nullable=true)
     */
    private $context;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_owner", type="boolean", nullable=false)
     */
    private $isOwner = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_write", type="boolean", nullable=false, options={"default"="1"})
     */
    private $allowWrite = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timeout_access", type="datetime", nullable=true)
     */
    private $timeoutAccess;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="set_by_user", referencedColumnName="id")
     * })
     */
    private $setByUser;

    /**
     * @var EcuParameterSets
     *
     * @ORM\ManyToOne(targetEntity="EcuParameterSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameter_set_id", referencedColumnName="ecu_parameter_set_id")
     * })
     */
    private $ecuParameterSet;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var Ecus
     *
     * @ORM\ManyToOne(targetEntity="Ecus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ecu_id")
     * })
     */
    private $ecu;

    public function getStsPrivilegId(): ?int
    {
        return $this->stsPrivilegId;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->isOwner;
    }

    public function setIsOwner(bool $isOwner): self
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    public function getAllowWrite(): ?bool
    {
        return $this->allowWrite;
    }

    public function setAllowWrite(bool $allowWrite): self
    {
        $this->allowWrite = $allowWrite;

        return $this;
    }

    public function getTimeoutAccess(): ?\DateTimeInterface
    {
        return $this->timeoutAccess;
    }

    public function setTimeoutAccess(?\DateTimeInterface $timeoutAccess): self
    {
        $this->timeoutAccess = $timeoutAccess;

        return $this;
    }

    public function getSetByUser(): ?Users
    {
        return $this->setByUser;
    }

    public function setSetByUser(?Users $setByUser): self
    {
        $this->setByUser = $setByUser;

        return $this;
    }

    public function getEcuParameterSet(): ?EcuParameterSets
    {
        return $this->ecuParameterSet;
    }

    public function setEcuParameterSet(?EcuParameterSets $ecuParameterSet): self
    {
        $this->ecuParameterSet = $ecuParameterSet;

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

    public function getEcu(): ?Ecus
    {
        return $this->ecu;
    }

    public function setEcu(?Ecus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }


}
