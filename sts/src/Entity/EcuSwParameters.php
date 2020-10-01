<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameters
 *
 * @ORM\Table(name="ecu_sw_parameters", indexes={@ORM\Index(name="IDX_43DA195DD0C0B53C", columns={"linked_to_coc_parameter_id"}), @ORM\Index(name="IDX_43DA195DA2AE29AC", columns={"used_default_value_id"}), @ORM\Index(name="IDX_43DA195D14AA1375", columns={"used_constant_value_id"}), @ORM\Index(name="IDX_43DA195D5622C5EF", columns={"dynamic_parameter_values_by_diagnostic_software_id"}), @ORM\Index(name="IDX_43DA195D64998C34", columns={"ecu_communication_protocol_id"}), @ORM\Index(name="IDX_43DA195DABA835F1", columns={"variable_type_id"}), @ORM\Index(name="IDX_43DA195DF8BD700D", columns={"unit_id"}), @ORM\Index(name="IDX_43DA195D8B84E6FC", columns={"linked_to_global_parameter_id"}), @ORM\Index(name="IDX_43DA195D47AAB085", columns={"ecu_sw_parameter_type_id"}), @ORM\Index(name="IDX_43DA195D8D3B41B6", columns={"ce_ecu_id"}), @ORM\Index(name="IDX_43DA195D3C4FC88E", columns={"ecu_software_parameter_name_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwParametersRepository")
 */
class EcuSwParameters
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameters_ecu_sw_parameter_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuSwParameterId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var float|null
     *
     * @ORM\Column(name="factor", type="float", precision=10, scale=0, nullable=true)
     */
    private $factor;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_value_linked_to_part_presence", type="boolean", nullable=true)
     */
    private $isValueLinkedToPartPresence;

    /**
     * @var string|null
     *
     * @ORM\Column(name="data_identifier", type="text", nullable=true)
     */
    private $dataIdentifier;

    /**
     * @var int|null
     *
     * @ORM\Column(name="start_bit", type="integer", nullable=true)
     */
    private $startBit = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="stop_bit", type="integer", nullable=true)
     */
    private $stopBit = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="number_of_bytes", type="integer", nullable=true, options={"default"="-1"})
     */
    private $numberOfBytes = '-1';

    /**
     * @var float|null
     *
     * @ORM\Column(name="parameter_offset", type="float", precision=10, scale=0, nullable=true, options={"default"="0.0"})
     */
    private $parameterOffset = '0.0';

    /**
     * @var int
     *
     * @ORM\Column(name="parameter_order", type="integer", nullable=false)
     */
    private $parameterOrder = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="should_read_parameter_value_from_ecu", type="boolean", nullable=false)
     */
    private $shouldReadParameterValueFromEcu;

    /**
     * @var bool
     *
     * @ORM\Column(name="should_write_parameter_value_to_ecu", type="boolean", nullable=false)
     */
    private $shouldWriteParameterValueToEcu;

    /**
     * @var bool
     *
     * @ORM\Column(name="should_confirm_parameter_value_from_ecu", type="boolean", nullable=false)
     */
    private $shouldConfirmParameterValueFromEcu;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="activated", type="boolean", nullable=true, options={"default"="1"})
     */
    private $activated = true;

    /**
     * @var string|null
     *
     * @ORM\Column(name="coding", type="text", nullable=true)
     */
    private $coding;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_big_endian", type="boolean", nullable=false)
     */
    private $isBigEndian = false;

    /**
     * @var CocParameters
     *
     * @ORM\ManyToOne(targetEntity="CocParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="linked_to_coc_parameter_id", referencedColumnName="coc_parameter_id")
     * })
     */
    private $linkedToCocParameter;

    /**
     * @var EcuSwParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="used_default_value_id", referencedColumnName="ecu_sw_parameter_value_set_id")
     * })
     */
    private $usedDefaultValue;

    /**
     * @var EcuSwParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="used_constant_value_id", referencedColumnName="ecu_sw_parameter_value_set_id")
     * })
     */
    private $usedConstantValue;

    /**
     * @var DynamicParameterValuesByDiagnosticSoftware
     *
     * @ORM\ManyToOne(targetEntity="DynamicParameterValuesByDiagnosticSoftware")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dynamic_parameter_values_by_diagnostic_software_id", referencedColumnName="dpvbds_id")
     * })
     */
    private $dynamicParameterValuesByDiagnosticSoftware;

    /**
     * @var EcuCommunicationProtocols
     *
     * @ORM\ManyToOne(targetEntity="EcuCommunicationProtocols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_communication_protocol_id", referencedColumnName="ecu_communication_protocol_id")
     * })
     */
    private $ecuCommunicationProtocol;

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
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id")
     * })
     */
    private $unit;

    /**
     * @var GlobalParameters
     *
     * @ORM\ManyToOne(targetEntity="GlobalParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="linked_to_global_parameter_id", referencedColumnName="global_parameter_id")
     * })
     */
    private $linkedToGlobalParameter;

    /**
     * @var EcuSwParameterTypes
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameterTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_parameter_type_id", referencedColumnName="ecu_sw_parameter_type_id")
     * })
     */
    private $ecuSwParameterType;

    /**
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ce_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ceEcu;

    /**
     * @var EcuSoftwareParameterNames
     *
     * @ORM\ManyToOne(targetEntity="EcuSoftwareParameterNames", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_software_parameter_name_id", referencedColumnName="ecu_software_parameter_name_id", onDelete="CASCADE")
     * })
     */
    private $ecuSoftwareParameterName;

    public function __clone() {
        $this->ecuSwParameterId = null;
    }

    public function getEcuSwParameterId(): ?int
    {
        return $this->ecuSwParameterId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFactor(): ?float
    {
        return $this->factor;
    }

    public function setFactor(?float $factor): self
    {
        $this->factor = $factor;

        return $this;
    }

    public function getIsValueLinkedToPartPresence(): ?bool
    {
        return $this->isValueLinkedToPartPresence;
    }

    public function setIsValueLinkedToPartPresence(?bool $isValueLinkedToPartPresence): self
    {
        $this->isValueLinkedToPartPresence = $isValueLinkedToPartPresence;

        return $this;
    }

    public function getDataIdentifier(): ?string
    {
        return $this->dataIdentifier;
    }

    public function setDataIdentifier(?string $dataIdentifier): self
    {
        $this->dataIdentifier = $dataIdentifier;

        return $this;
    }

    public function getStartBit(): ?int
    {
        return $this->startBit;
    }

    public function setStartBit(?int $startBit): self
    {
        $this->startBit = $startBit;

        return $this;
    }

    public function getStopBit(): ?int
    {
        return $this->stopBit;
    }

    public function setStopBit(?int $stopBit): self
    {
        $this->stopBit = $stopBit;

        return $this;
    }

    public function getNumberOfBytes(): ?int
    {
        return $this->numberOfBytes;
    }

    public function setNumberOfBytes(?int $numberOfBytes): self
    {
        $this->numberOfBytes = $numberOfBytes;

        return $this;
    }

    public function getParameterOffset(): ?float
    {
        return $this->parameterOffset;
    }

    public function setParameterOffset(?float $parameterOffset): self
    {
        $this->parameterOffset = $parameterOffset;

        return $this;
    }

    public function getParameterOrder(): ?int
    {
        return $this->parameterOrder;
    }

    public function setParameterOrder(int $parameterOrder): self
    {
        $this->parameterOrder = $parameterOrder;

        return $this;
    }

    public function getShouldReadParameterValueFromEcu(): ?bool
    {
        return $this->shouldReadParameterValueFromEcu;
    }

    public function setShouldReadParameterValueFromEcu(bool $shouldReadParameterValueFromEcu): self
    {
        $this->shouldReadParameterValueFromEcu = $shouldReadParameterValueFromEcu;

        return $this;
    }

    public function getShouldWriteParameterValueToEcu(): ?bool
    {
        return $this->shouldWriteParameterValueToEcu;
    }

    public function setShouldWriteParameterValueToEcu(bool $shouldWriteParameterValueToEcu): self
    {
        $this->shouldWriteParameterValueToEcu = $shouldWriteParameterValueToEcu;

        return $this;
    }

    public function getShouldConfirmParameterValueFromEcu(): ?bool
    {
        return $this->shouldConfirmParameterValueFromEcu;
    }

    public function setShouldConfirmParameterValueFromEcu(bool $shouldConfirmParameterValueFromEcu): self
    {
        $this->shouldConfirmParameterValueFromEcu = $shouldConfirmParameterValueFromEcu;

        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(?bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getCoding(): ?string
    {
        return $this->coding;
    }

    public function setCoding(?string $coding): self
    {
        $this->coding = $coding;

        return $this;
    }

    public function getIsBigEndian(): ?bool
    {
        return $this->isBigEndian;
    }

    public function setIsBigEndian(bool $isBigEndian): self
    {
        $this->isBigEndian = $isBigEndian;

        return $this;
    }

    public function getLinkedToCocParameter(): ?CocParameters
    {
        return $this->linkedToCocParameter;
    }

    public function setLinkedToCocParameter(?CocParameters $linkedToCocParameter): self
    {
        $this->linkedToCocParameter = $linkedToCocParameter;

        return $this;
    }

    public function getUsedDefaultValue(): ?EcuSwParameterValuesSets
    {
        return $this->usedDefaultValue;
    }

    public function setUsedDefaultValue(?EcuSwParameterValuesSets $usedDefaultValue): self
    {
        $this->usedDefaultValue = $usedDefaultValue;

        return $this;
    }

    public function getUsedConstantValue(): ?EcuSwParameterValuesSets
    {
        return $this->usedConstantValue;
    }

    public function setUsedConstantValue(?EcuSwParameterValuesSets $usedConstantValue): self
    {
        $this->usedConstantValue = $usedConstantValue;

        return $this;
    }

    public function getDynamicParameterValuesByDiagnosticSoftware(): ?DynamicParameterValuesByDiagnosticSoftware
    {
        return $this->dynamicParameterValuesByDiagnosticSoftware;
    }

    public function setDynamicParameterValuesByDiagnosticSoftware(?DynamicParameterValuesByDiagnosticSoftware $dynamicParameterValuesByDiagnosticSoftware): self
    {
        $this->dynamicParameterValuesByDiagnosticSoftware = $dynamicParameterValuesByDiagnosticSoftware;

        return $this;
    }

    public function getEcuCommunicationProtocol(): ?EcuCommunicationProtocols
    {
        return $this->ecuCommunicationProtocol;
    }

    public function setEcuCommunicationProtocol(?EcuCommunicationProtocols $ecuCommunicationProtocol): self
    {
        $this->ecuCommunicationProtocol = $ecuCommunicationProtocol;

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

    public function getUnit(): ?Units
    {
        return $this->unit;
    }

    public function setUnit(?Units $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getLinkedToGlobalParameter(): ?GlobalParameters
    {
        return $this->linkedToGlobalParameter;
    }

    public function setLinkedToGlobalParameter(?GlobalParameters $linkedToGlobalParameter): self
    {
        $this->linkedToGlobalParameter = $linkedToGlobalParameter;

        return $this;
    }

    public function getEcuSwParameterType(): ?EcuSwParameterTypes
    {
        return $this->ecuSwParameterType;
    }

    public function setEcuSwParameterType(?EcuSwParameterTypes $ecuSwParameterType): self
    {
        $this->ecuSwParameterType = $ecuSwParameterType;

        return $this;
    }

    public function getCeEcu(): ?ConfigurationEcus
    {
        return $this->ceEcu;
    }

    public function setCeEcu(?ConfigurationEcus $ceEcu): self
    {
        $this->ceEcu = $ceEcu;

        return $this;
    }

    public function getEcuSoftwareParameterName(): ?EcuSoftwareParameterNames
    {
        return $this->ecuSoftwareParameterName;
    }

    public function setEcuSoftwareParameterName(?EcuSoftwareParameterNames $ecuSoftwareParameterName): self
    {
        $this->ecuSoftwareParameterName = $ecuSoftwareParameterName;

        return $this;
    }
}
