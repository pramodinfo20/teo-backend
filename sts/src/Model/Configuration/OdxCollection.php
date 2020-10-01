<?php

namespace App\Model\Configuration;

use Doctrine\Common\Collections\Collection;

interface OdxCollection
{
    public function getParameters(): Collection;

    public function addParameters(OdxParameter $parameter);

}