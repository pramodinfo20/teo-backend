<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QsFaultCatInputs
 *
 * @ORM\Table(name="qs_fault_cat_inputs")
 * @ORM\Entity
 */
class QsFaultCatInputs
{
    /**
     * @var int
     *
     * @ORM\Column(name="qs_fcat_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $qsFcatId;

    /**
     * @var string
     *
     * @ORM\Column(name="field_key", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $fieldKey;

    /**
     * @var string
     *
     * @ORM\Column(name="field_label", type="text", nullable=false)
     */
    private $fieldLabel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="field_type", type="text", nullable=true)
     */
    private $fieldType;

    /**
     * @var int
     *
     * @ORM\Column(name="show_order", type="integer", nullable=false, options={"default"="1"})
     */
    private $showOrder = '1';

    /**
     * @var string|null
     *
     * @ORM\Column(name="parent_field_key", type="text", nullable=true)
     */
    private $parentFieldKey;

    public function getQsFcatId(): ?int
    {
        return $this->qsFcatId;
    }

    public function getFieldKey(): ?string
    {
        return $this->fieldKey;
    }

    public function getFieldLabel(): ?string
    {
        return $this->fieldLabel;
    }

    public function setFieldLabel(string $fieldLabel): self
    {
        $this->fieldLabel = $fieldLabel;

        return $this;
    }

    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    public function setFieldType(?string $fieldType): self
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    public function getShowOrder(): ?int
    {
        return $this->showOrder;
    }

    public function setShowOrder(int $showOrder): self
    {
        $this->showOrder = $showOrder;

        return $this;
    }

    public function getParentFieldKey(): ?string
    {
        return $this->parentFieldKey;
    }

    public function setParentFieldKey(?string $parentFieldKey): self
    {
        $this->parentFieldKey = $parentFieldKey;

        return $this;
    }


}
