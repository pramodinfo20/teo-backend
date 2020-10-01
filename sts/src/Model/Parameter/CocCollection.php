<?php

namespace App\Model\Parameter;

use App\Model\ConvertibleToHistoryI;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class CocCollection implements \Countable, ConvertibleToHistoryI
{
    /**
     * @var CocParameter[]
     */
    private $parameters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    /**
     * @return Collection|CocParameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameters(CocParameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }

        return $this;
    }

    public function removeParameters(CocParameter $parameter): self
    {
        if ($this->parameters->contains($parameter)) {
            $this->parameters->removeElement($parameter);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->parameters->count();
    }
}