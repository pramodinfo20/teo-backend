<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/6/19
 * Time: 1:41 PM
 */

namespace App\Service\Ecu\Sw;

use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class SubVehicleConfiguration
{

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * Get SubVehicleConfigurations by Sw id
     *
     * @param int $sw
     *
     * @return array
     */
    public function getSubVehicleConfigurationsBySwId(int $sw): array
    {
        $entityManagerSubConfigurationMapping =
            $this->entityManager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class);

        $subConfigurationsMap = $entityManagerSubConfigurationMapping->findBy(['ecuSwVersion' => $sw]);

        $subConfigs = [];
        $iterator = 1;
        foreach ($subConfigurationsMap as $subConfigurationMap) {
            if ($iterator > 20) {
                break;
            }
            $subConfiguration = $subConfigurationMap->getSubVehicleConfiguration();
            array_push($subConfigs, $subConfiguration->getSubVehicleConfigurationName());
            ++$iterator;
        }

        return $subConfigs;
    }
}