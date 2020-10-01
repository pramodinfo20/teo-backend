<?php

namespace App\Model\EcuSwProperties;

use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Intl\Exception\NotImplementedException;

class EcuSwPropertiesCollection implements EcuSwPropertiesCollectionI, Countable
{
    /**
     * @var EcuSwPropertiesModel[]
     */
    private $properties;

    /**
     * EcuSwPropertiesCollection constructor.
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    /**
     * @return Collection|EcuSwPropertiesModel[]
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    /**
     * @param EcuSwPropertiesModelI $ecuSwPropertiesModel
     * @return EcuSwPropertiesCollection
     */
    public function addProperties(EcuSwPropertiesModelI $ecuSwPropertiesModel): self
    {
        if(!$this->properties->contains($ecuSwPropertiesModel)) {
            $this->properties[] = $ecuSwPropertiesModel;
        }

        return $this;
    }

    /**
     * @param EcuSwPropertiesModelI $ecuSwPropertiesModel
     * @return EcuSwPropertiesCollection
     */
    public function removeProperties(EcuSwPropertiesModelI $ecuSwPropertiesModel): self
    {
        if($this->properties->contains($ecuSwPropertiesModel)){
            $this->properties->removeElement($ecuSwPropertiesModel);
        }

        return $this;
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return $this->properties->count();
    }
}