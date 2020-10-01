<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CocParameters
 *
 * @ORM\Table(name="coc_parameters", uniqueConstraints={@ORM\UniqueConstraint(name="coc_parameters_coc_parameter_name_key", columns={"coc_parameter_name"})}, indexes={@ORM\Index(name="IDX_59176BCCF8BD700D", columns={"unit_id"}), @ORM\Index(name="IDX_59176BCCE98FD210", columns={"deputy_user_id"}), @ORM\Index(name="IDX_59176BCCBDAD1998", columns={"responsible_user_id"}), @ORM\Index(name="IDX_59176BCCABA835F1", columns={"variable_type_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CocParametersRepository")
 */
class CocParameters
{
    /**
     * @var int
     *
     * @ORM\Column(name="coc_parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="coc_parameters_coc_parameter_id_seq", allocationSize=1, initialValue=1)
     */
    private $cocParameterId;

    /**
     * @var string
     *
     * @ORM\Column(name="coc_parameter_name", type="text", nullable=false)
     */
    private $cocParameterName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="section", type="text", nullable=true)
     */
    private $section;

    /**
     * @var string|null
     *
     * @ORM\Column(name="field", type="text", nullable=true)
     */
    private $field;

    /**
     * @var int
     *
     * @ORM\Column(name="parameter_order", type="integer", nullable=false, options={"default"="-1"})
     */
    private $parameterOrder = '-1';

    /**
     * @var \Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id")
     * })
     */
    private $unit;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deputy_user_id", referencedColumnName="id")
     * })
     */
    private $deputyUser;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsible_user_id", referencedColumnName="id")
     * })
     */
    private $responsibleUser;

    /**
     * @var \VariableTypes
     *
     * @ORM\ManyToOne(targetEntity="VariableTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="variable_type_id", referencedColumnName="variable_type_id")
     * })
     */
    private $variableType;

    /**
     * @var ArrayCollection
     */
    private $linkedEcuParameters;

    public function getCocParameterId(): ?int
    {
        return $this->cocParameterId;
    }

    public function getCocParameterName(): ?string
    {
        return $this->cocParameterName;
    }

    public function setCocParameterName(string $cocParameterName): self
    {
        $this->cocParameterName = $cocParameterName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

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

    public function getUnit(): ?Units
    {
        return $this->unit;
    }

    public function setUnit(?Units $unit): self
    {
        $this->unit = $unit;

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
        return $this->cocParameterName;
    }
}
