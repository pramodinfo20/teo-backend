<?php


namespace App\Model\EcuSwProperties;


use Doctrine\Common\Collections\Collection;

interface EcuSwPropertiesCollectionI
{
    public function getProperties(): Collection;

    public function addProperties(EcuSwPropertiesModelI $ecuSwPropertiesModel);

    public function removeProperties(EcuSwPropertiesModelI $ecuSwPropertiesModel);
}