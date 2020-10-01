<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeliveryPlanVariantValues
 *
 * @ORM\Table(name="delivery_plan_variant_values")
 * @ORM\Entity
 */
class DeliveryPlanVariantValues
{
    /**
     * @var int
     *
     * @ORM\Column(name="delivery_plan_variant_values_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="delivery_plan_variant_values_delivery_plan_variant_values_id_seq", allocationSize=1,
     *                                                                                                         initialValue=1)
     */
    private $deliveryPlanVariantValuesId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="external_variant_value", type="integer", nullable=true)
     */
    private $externalVariantValue;

    /**
     * @var int|null
     *
     * @ORM\Column(name="internal_variant_value", type="integer", nullable=true)
     */
    private $internalVariantValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="variant_external_name", type="text", nullable=true)
     */
    private $variantExternalName;

    public function getDeliveryPlanVariantValuesId(): ?int
    {
        return $this->deliveryPlanVariantValuesId;
    }

    public function getExternalVariantValue(): ?int
    {
        return $this->externalVariantValue;
    }

    public function setExternalVariantValue(?int $externalVariantValue): self
    {
        $this->externalVariantValue = $externalVariantValue;

        return $this;
    }

    public function getInternalVariantValue(): ?int
    {
        return $this->internalVariantValue;
    }

    public function setInternalVariantValue(?int $internalVariantValue): self
    {
        $this->internalVariantValue = $internalVariantValue;

        return $this;
    }

    public function getVariantExternalName(): ?string
    {
        return $this->variantExternalName;
    }

    public function setVariantExternalName(?string $variantExternalName): self
    {
        $this->variantExternalName = $variantExternalName;

        return $this;
    }


}
