<?php

namespace App\Model\Diagnostic;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class DynamicParametersCollection implements \Countable
{
    /**
     * @var DynamicParameter[]
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
     * @return Collection|DynamicParameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameters(DynamicParameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }

        return $this;
    }

    public function removeParameters(DynamicParameter $parameter): self
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