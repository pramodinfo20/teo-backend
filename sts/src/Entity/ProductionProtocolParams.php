<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductionProtocolParams
 *
 * @ORM\Table(name="production_protocol_params")
 * @ORM\Entity
 */
class ProductionProtocolParams
{
    /**
     * @var int
     *
     * @ORM\Column(name="param_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="production_protocol_params_param_id_seq", allocationSize=1, initialValue=1)
     */
    private $paramId;

    /**
     * @var string
     *
     * @ORM\Column(name="param_key", type="text", nullable=false)
     */
    private $paramKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="param_value", type="text", nullable=true)
     */
    private $paramValue;

    public function getParamId(): ?int
    {
        return $this->paramId;
    }

    public function getParamKey(): ?string
    {
        return $this->paramKey;
    }

    public function setParamKey(string $paramKey): self
    {
        $this->paramKey = $paramKey;

        return $this;
    }

    public function getParamValue(): ?string
    {
        return $this->paramValue;
    }

    public function setParamValue(?string $paramValue): self
    {
        $this->paramValue = $paramValue;

        return $this;
    }


}
