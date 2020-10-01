<?php

namespace App\Service\Configuration;


use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwParameterEcuSwVersionMappingOverwrite;
use App\Entity\EcuSwVersionLockStatus;
use App\Entity\EcuSwVersions;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\SubVehicleConfigurations;
use App\Service\History\Configuration\HistorySoftwareVersionI;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class SoftwareVersion implements HistorySoftwareVersionI
{
    const NOT_LOCKED = 1;
    const LOCKED = 2;

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
     * Assign Sw to SubConfiguration
     *
     * @param SubVehicleConfigurations $subconfiguration
     * @param EcuSwVersions            $sw
     * @param ConfigurationEcus        $ecu
     * @param bool                     $primary
     *
     * @return void
     */
    public function assignSw(SubVehicleConfigurations $subconfiguration, EcuSwVersions $sw, ConfigurationEcus $ecu, bool $primary): void
    {
        $mappingManager = $this->manager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class);
        $overwriteManager = $this->manager->getRepository(EcuSwParameterEcuSwVersionMappingOverwrite::class);
        $lockManager = $this->manager->getRepository(EcuSwVersionLockStatus::class);

        $mappedSws = $mappingManager->findBy(['subVehicleConfiguration' => $subconfiguration, 'isPrimarySw' => $primary]);

        $sw->setEcuSwVersionLockStatus($lockManager->find(self::LOCKED));

        $mappedSw = [];
        if (!empty($mappedSws)) {
            $mappedSw = array_filter($mappedSws, function ($row) use ($ecu)
            {
                return $row->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu->getCeEcuId();
            });
            if (!empty($mappedSw)) {
                $overwrittenValues = $overwriteManager->findBy(['subVehicleConfiguration' => $subconfiguration, 'ecuSwVersion' => reset($mappedSw)->getEcuSwVersion()]);
                foreach ($overwrittenValues as $mapped) {
                    $this->manager->remove($mapped->getEcuSwParameterValueSet());
                }
                $this->manager->remove(reset($mappedSw));
            }
        }

        $newMappedSw = new EcuSwVersionSubVehicleConfigurationMapping();

        $newMappedSw->setSubVehicleConfiguration($subconfiguration);
        $newMappedSw->setEcuSwVersion($sw);
        $newMappedSw->setIsPrimarySw($primary);

        $this->manager->persist($sw);
        $this->manager->persist($newMappedSw);
        $this->manager->flush();
    }

    /**
     * Remove Sw assignment
     *
     * @param SubVehicleConfigurations $subconfiguration
     * @param ConfigurationEcus        $ecu
     * @param bool                     $primary
     *
     * @return EcuSwVersions|null
     */
    public function removeSwAssignment(
        SubVehicleConfigurations $subconfiguration,
        ConfigurationEcus $ecu,
        bool $primary): ?EcuSwVersions
    {
        $mappingManager = $this->manager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class);
        $overwriteManager = $this->manager->getRepository(EcuSwParameterEcuSwVersionMappingOverwrite::class);
        $lockManager = $this->manager->getRepository(EcuSwVersionLockStatus::class);

        $mappedSws = $mappingManager->findBy(['subVehicleConfiguration' => $subconfiguration, 'isPrimarySw' => $primary]);

        $mappedSw = [];
        $removedSw = null;
        if (!empty($mappedSws)) {
            $mappedSw = array_filter($mappedSws, function ($row) use ($ecu)
            {
                return $row->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu->getCeEcuId();
            });
            if (!empty($mappedSw)) {
                $overwrittenValues = $overwriteManager->findBy(['subVehicleConfiguration' => $subconfiguration, 'ecuSwVersion' => reset($mappedSw)->getEcuSwVersion()]);
                foreach ($overwrittenValues as $mapped) {
                    $this->manager->remove($mapped->getEcuSwParameterValueSet());
                }
                $removedSw = clone reset($mappedSw);
                $this->manager->remove(reset($mappedSw));
            }
        }

        $this->manager->flush();

        $allSws = [];
        if (!empty($mappedSw)) {
            $allSws = $mappingManager->findBy(['ecuSwVersion' => reset($mappedSw)->getEcuSwVersion()]);
        }

        if (empty($allSws)) {
            $sw = reset($mappedSw)->getEcuSwVersion();
            $sw->setEcuSwVersionLockStatus($lockManager->find(self::NOT_LOCKED));

            $this->manager->persist($sw);
            $this->manager->flush();
        }

        return is_null($removedSw) ? $removedSw : $removedSw->getEcuSwVersion();
    }
}