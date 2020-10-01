<?php

namespace App\Model\History;

use App\Model\History\HistoryI;
use App\Model\History\Traits\HistoryEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class HistoryOdx2Collection implements HistoryOdxCollection, \Countable, HistoryI
{
    use HistoryEvent;

    /**
     * @var HistoryOdx2Parameter[]
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
     * @return Collection|HistoryOdx2Parameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameters(HistoryOdxParameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
        }

        return $this;
    }

    public function removeParameters(HistoryOdxParameter $parameter): self
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