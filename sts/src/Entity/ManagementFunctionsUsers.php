<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ManagementFunctionsUsers
 *
 * @ORM\Table(name="management_functions_users")
 * @ORM\Entity
 */
class ManagementFunctionsUsers
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="management_functions_users_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="function_id", type="integer", nullable=false)
     */
    private $functionId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_structure", type="boolean", nullable=true)
     */
    private $isStructure = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="structure_details", type="text", nullable=true)
     */
    private $structureDetails;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFunctionId(): ?int
    {
        return $this->functionId;
    }

    public function setFunctionId(int $functionId): self
    {
        $this->functionId = $functionId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getIsStructure(): ?bool
    {
        return $this->isStructure;
    }

    public function setIsStructure(?bool $isStructure): self
    {
        $this->isStructure = $isStructure;

        return $this;
    }

    public function getStructureDetails(): ?string
    {
        return $this->structureDetails;
    }

    public function setStructureDetails(?string $structureDetails): self
    {
        $this->structureDetails = $structureDetails;

        return $this;
    }


}
