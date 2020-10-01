<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalParameters
 *
 * @ORM\Table(name="global_parameters", uniqueConstraints={@ORM\UniqueConstraint(name="global_parameters_global_parameter_name_key", columns={"global_parameter_name"})}, indexes={@ORM\Index(name="IDX_8F9C6275BDAD1998", columns={"responsible_user_id"}), @ORM\Index(name="IDX_8F9C6275ABA835F1", columns={"variable_type_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\GlobalParametersRepository")
 */
class GlobalParameters
{
    /**
     * @var int
     *
     * @ORM\Column(name="global_parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="global_parameters_global_parameter_id_seq", allocationSize=1,
     *                                                                                  initialValue=1)
     */
    private $globalParameterId;

    /**
     * @var string
     *
     * @ORM\Column(name="global_parameter_name", type="text", nullable=false)
     */
    private $globalParameterName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="min_value", type="text", nullable=true)
     */
    private $minValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="max_value", type="text", nullable=true)
     */
    private $maxValue;

    /**
     * @var GlobalParameterValuesSets
     *
     * @ORM\OneToOne(targetEntity="GlobalParameterValuesSets", mappedBy="globalParameter", cascade={"persist"})
     */
    private $value;

    /**
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="global_unit_id", referencedColumnName="unit_id")
     * })
     */
    private $globalUnit;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsible_user_id", referencedColumnName="id")
     * })
     */
    private $responsibleUser;

    /**
     * @var VariableTypes
     *
     * @ORM\ManyToOne(targetEntity="VariableTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="variable_type_id", referencedColumnName="variable_type_id")
     * })
     */
    private $variableType;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="charging_control_related", type="boolean", nullable=true)
     */
    private $chargingControlRelated;

    /**
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $specialEcu;

    /**
     * @var ArrayCollection
     */
    private $linkedEcuParameters;

    public function getGlobalParameterId(): ?int
    {
        return $this->globalParameterId;
    }

    public function getGlobalParameterName(): ?string
    {
        return $this->globalParameterName;
    }

    public function setGlobalParameterName(string $globalParameterName): self
    {
        $this->globalParameterName = $globalParameterName;

        return $this;
    }

    public function getMinValue(): ?string
    {
        return $this->minValue;
    }

    public function setMinValue(?string $minValue): self
    {
        $this->minValue = $minValue;

        return $this;
    }

    public function getMaxValue(): ?string
    {
        return $this->maxValue;
    }

    public function setMaxValue(?string $maxValue): self
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    public function getValue(): ?GlobalParameterValuesSets
    {
        return $this->value;
    }

    public function setValue(?GlobalParameterValuesSets $value): void
    {
        /* Set manually relation */
        $value->setGlobalParameter($this);

        $this->value = $value;
    }

    public function getGlobalUnit(): ?Units
    {
        return $this->globalUnit;
    }

    public function setGlobalUnit(?Units $globalUnit): void
    {
        $this->globalUnit = $globalUnit;
    }

    public function getResponsibleUser(): ?Users
    {
        return $this->responsibleUser;
    }

    public function setResponsibleUser(?Users $responsibleUser): self
    {
        $this->responsibleUser = $responsibleUser;

        return $this;
    }

    public function getVariableType(): ?VariableTypes
    {
        return $this->variableType;
    }

    public function setVariableType(?VariableTypes $variableType): self
    {
        $this->variableType = $variableType;

        return $this;
    }

    public function getLinkedEcuParameters(): ?ArrayCollection
    {
        return $this->linkedEcuParameters;
    }

    public function setLinkedEcuParameters(array $linkedEcuParameters = null): self
    {
        if (is_array($linkedEcuParameters)) {
            $this->linkedEcuParameters = new ArrayCollection();

            foreach ($linkedEcuParameters as $linkedEcuParameter) {
                $this->linkedEcuParameters->add($linkedEcuParameter);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->globalParameterName;
    }

    /**
     * @return ConfigurationEcus
     */
    public function getSpecialEcu(): ?ConfigurationEcus
    {
        return $this->specialEcu;
    }

    /**
     * @param ConfigurationEcus $specialEcu
     */
    public function setSpecialEcu(?ConfigurationEcus $specialEcu): void
    {
        $this->specialEcu = $specialEcu;
    }

    /**
     * @return bool|null
     */
    public function getChargingControlRelated(): ?bool
    {
        return $this->chargingControlRelated;
    }

    /**
     * @param bool|null $chargingControlRelated
     */
    public function setChargingControlRelated(?bool $chargingControlRelated): void
    {
        $this->chargingControlRelated = $chargingControlRelated;
    }
}
