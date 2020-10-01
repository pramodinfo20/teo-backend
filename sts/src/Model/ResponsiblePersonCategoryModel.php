<?php


namespace App\Model;


class ResponsiblePersonCategoryModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isSelected;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isIsSelected(): bool
    {
        return $this->isSelected;
    }

    /**
     * @param bool $isSelected
     */
    public function setIsSelected(bool $isSelected): void
    {
        $this->isSelected = $isSelected;
    }


}