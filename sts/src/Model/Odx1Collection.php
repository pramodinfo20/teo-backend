<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Odx1Collection implements OdxCollection, \Countable, ConvertibleToHistoryI
{
    /**
     * @var Odx1Parameter[]
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
     * @return Collection|Odx1Parameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameters(OdxParameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }

        return $this;
    }

    public function removeParameters(OdxParameter $parameter): self
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