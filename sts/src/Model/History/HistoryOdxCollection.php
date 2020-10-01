<?php


namespace App\Model\History;


use Doctrine\Common\Collections\Collection;

interface HistoryOdxCollection
{
    public function getParameters(): Collection;
    public function addParameters(HistoryOdxParameter $parameter);
    public function removeParameters(HistoryOdxParameter $parameter);
}
