<?php

namespace App\Service\History\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\EcuCommunicationProtocols;
use App\Enum\Entity\HistoryEvents;
use App\Model\Configuration\Header as HeaderModel;
use App\Service\Configuration\Header;
use App\Service\Configuration\Parameter;
use App\Service\Configuration\SoftwareVersion;
use App\Service\Ecu\Sw\SoftwareVersion as SoftwareVersion2;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Configuration\Odx2Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistorySoftwareVersion implements HistorySoftwareVersionI
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
     * @var HistoryStrategy
     */
    private $history;

    /**
     * @var SoftwareVersion
     */
    private $softwareVersionService;

    /**
     * @var Header
     */
    private $headerService;

    /**
     * @var Parameter
     */
    private $parameterService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param SoftwareVersion        $softwareVersionService
     * @param Header                 $headerService
     * @param Parameter              $parameterService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        SoftwareVersion  $softwareVersionService,
        Header $headerService,
        Parameter $parameterService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::SOFTWARE_MANAGEMENT);
        $this->softwareVersionService = $softwareVersionService;
        $this->headerService = $headerService;
        $this->parameterService = $parameterService;
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
     * @throws \Exception
     */
    public function assignSw(
        SubVehicleConfigurations $subconfiguration,
        EcuSwVersions $sw,
        ConfigurationEcus $ecu,
        bool $primary
    ): void
    {
        $beforeHeader =  new HeaderModel();
        $beforeOdxCollection = new Odx2Collection();

        try {
            $this->softwareVersionService->assignSw($subconfiguration, $sw, $ecu, $primary);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($sw, $subconfiguration);

        $afterOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocols::getProtocolNameById
            (SoftwareVersion2::DEFAULT_PROTOCOL), $sw,
            $subconfiguration
        );


        try {
            $this->history->init(
                $subconfiguration->getSubVehicleConfigurationId(),
                $this->history->prepareName($sw, $subconfiguration)
            );
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::CREATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::CREATE);
            $this->history->setIsPrimary($primary);
            $this->history->closeSession();
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Remove Sw assignment
     *
     * @param SubVehicleConfigurations $subconfiguration
     * @param ConfigurationEcus        $ecu
     * @param bool                     $primary
     *
     * @return EcuSwVersions|null
     * @throws \Exception
     */
    public function removeSwAssignment(
        SubVehicleConfigurations $subconfiguration,
        ConfigurationEcus $ecu,
        bool $primary
    ): ?EcuSwVersions
    {
        $mappingManager = $this->manager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class);

        $mappedSws = $mappingManager->findBy(['subVehicleConfiguration' => $subconfiguration, 'isPrimarySw' => $primary]);

        $mappedSw = [];
        $removedMappedSw = null;

        if (!empty($mappedSws)) {
            $mappedSw = array_filter($mappedSws, function ($row) use ($ecu)
            {
                return $row->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu->getCeEcuId();
            });
            if (!empty($mappedSw)) {
                $removedMappedSw = clone reset($mappedSw);
            }
        }

        $beforeHeader =  $this->headerService->getHeader($removedMappedSw->getEcuSwVersion(), $subconfiguration);

        $beforeOdxCollection = $this->parameterService->getParameters(
            2, EcuCommunicationProtocols::getProtocolNameById
        (SoftwareVersion2::DEFAULT_PROTOCOL), $removedMappedSw->getEcuSwVersion(),
            $subconfiguration
        );


        try {
            $removedSw = $this->softwareVersionService->removeSwAssignment($subconfiguration, $ecu, $primary);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  new HeaderModel();
        $afterOdxCollection = new Odx2Collection();

        try {
            $this->history->init(
                $subconfiguration->getSubVehicleConfigurationId(),
                $this->history->prepareName($removedSw, $subconfiguration)
            );
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::DELETE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::DELETE);
            $this->history->setIsPrimary($primary);
            $this->history->closeSession();
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $removedSw;
    }
}