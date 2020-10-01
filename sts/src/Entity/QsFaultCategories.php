<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QsFaultCategories
 *
 * @ORM\Table(name="qs_fault_categories")
 * @ORM\Entity
 */
class QsFaultCategories
{
    /**
     * @var int
     *
     * @ORM\Column(name="qs_fcat_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="qs_fault_categories_qs_fcat_id_seq", allocationSize=1, initialValue=1)
     */
    private $qsFcatId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cat_label", type="text", nullable=true)
     */
    private $catLabel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent_cat", type="integer", nullable=true)
     */
    private $parentCat;

    public function getQsFcatId(): ?int
    {
        return $this->qsFcatId;
    }

    public function getCatLabel(): ?string
    {
        return $this->catLabel;
    }

    public function setCatLabel(?string $catLabel): self
    {
        $this->catLabel = $catLabel;

        return $this;
    }

    public function getParentCat(): ?int
    {
        return $this->parentCat;
    }

    public function setParentCat(?int $parentCat): self
    {
        $this->parentCat = $parentCat;

        return $this;
    }


}
