<?php

namespace App\Factory;

use App\Entity\SubVehicleConfigurations;
use App\Service\Vehicles\Configuration\LongKey;
use App\Service\Vehicles\Configuration\ShortKey;
use Doctrine\Common\Persistence\ObjectManager;


/**
 * Class VehicleConfigurationCreator
 *
 * @package App\Factory
 */
class VehicleConfigurationCreator
{
    /**
     * @var LongKey
     */
    private $after;
    private $before;

    private $manager;

    public function __construct(LongKey $after, ShortKey $before, ObjectManager $manager)
    {
        $this->after = $after;
        $this->before = $before;
        $this->manager = $manager;
    }

    public function create($subConfigId)
    {
        $typeYearSeries = $this->manager->getRepository(SubVehicleConfigurations::class)->getTypeSeriesYear($subConfigId);
        $versionOfConfiguration = $this->checkKeyVersion($typeYearSeries);

        switch ($versionOfConfiguration) {
            case 'predefinedOld':
                $this->before->setSubConfigId($subConfigId);
                return $this->before;
                break;
            case 'predefinedNew':
                $this->after->setSubConfigId($subConfigId);
                return $this->after;
                break;
            case 'custom':
            default:
                return null;
                break;
        }
    }

    private function checkKeyVersion($keyToDecode)
    {

        if ($keyToDecode['type'] == 'D' AND $keyToDecode['year'] >= 17 AND $keyToDecode['series'] >= 2)
            return 'predefinedNew';
        else
            return 'predefinedOld';
    }


}

