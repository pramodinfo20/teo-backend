<?php


namespace App\Model\EcuSwProperties;


class EcuSwPropertiesModel implements EcuSwPropertiesModelI
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $order;

    /**
     * @var bool
     */
    private $isAssigned = false;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id = null): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value = null): void
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order = null): void
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isAssigned(): bool
    {
        return $this->isAssigned;
    }

    /**
     * @param bool $isAssigned
     */
    public function setIsAssigned(bool $isAssigned = false): void
    {
        $this->isAssigned = $isAssigned;
    }
}