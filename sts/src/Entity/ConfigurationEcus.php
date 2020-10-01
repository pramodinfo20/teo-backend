<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigurationEcus
 *
 * @ORM\Table(name="configuration_ecus", uniqueConstraints={@ORM\UniqueConstraint(name="configuration_ecus_ecu_name_key", columns={"ecu_name"})}, indexes={@ORM\Index(name="IDX_993A7666E98FD210", columns={"deputy_user_id"}), @ORM\Index(name="IDX_993A7666C54519FE", columns={"responsible_person_user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ConfigurationEcusRepository")
 */
class ConfigurationEcus
{
    /**
     * @var int
     *
     * @ORM\Column(name="ce_ecu_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="configuration_ecus_ce_ecu_id_seq", allocationSize=1, initialValue=1)
     */
    private $ceEcuId;

    /**
     * @var string
     *
     * @ORM\Column(name="ecu_name", type="text", nullable=false)
     */
    private $ecuName;

    /**
     * @var bool
     *
     * @ORM\Column(name="diagnostic_software_supports_sts_odx2_for_this_ecu", type="boolean", nullable=false)
     */
    private $diagnosticSoftwareSupportsStsOdx2ForThisEcu = false;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deputy_user_id", referencedColumnName="id")
     * })
     */
    private $deputyUser;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsible_person_user_id", referencedColumnName="id")
     * })
     */
    private $responsiblePersonUser;

    public function getCeEcuId(): ?int
    {
        return $this->ceEcuId;
    }

    public function getEcuName(): ?string
    {
        return $this->ecuName;
    }

    public function setEcuName(string $ecuName): self
    {
        $this->ecuName = $ecuName;

        return $this;
    }

    public function getDiagnosticSoftwareSupportsStsOdx2ForThisEcu(): ?bool
    {
        return $this->diagnosticSoftwareSupportsStsOdx2ForThisEcu;
    }

    public function setDiagnosticSoftwareSupportsStsOdx2ForThisEcu(bool $diagnosticSoftwareSupportsStsOdx2ForThisEcu): self
    {
        $this->diagnosticSoftwareSupportsStsOdx2ForThisEcu = $diagnosticSoftwareSupportsStsOdx2ForThisEcu;

        return $this;
    }

    public function getDeputyUser(): ?Users
    {
        return $this->deputyUser;
    }

    public function setDeputyUser(?Users $deputyUser): self
    {
        $this->deputyUser = $deputyUser;

        return $this;
    }

    public function getResponsiblePersonUser(): ?Users
    {
        return $this->responsiblePersonUser;
    }

    public function setResponsiblePersonUser(?Users $responsiblePersonUser): self
    {
        $this->responsiblePersonUser = $responsiblePersonUser;

        return $this;
    }


}
