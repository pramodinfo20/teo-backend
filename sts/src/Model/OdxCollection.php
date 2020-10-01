<?php

namespace App\Model;

use Doctrine\Common\Collections\Collection;

interface OdxCollection
{
    public function getParameters(): Collection;

    public function addParameters(OdxParameter $parameter);

    public function removeParameters(OdxParameter $parameter);
}