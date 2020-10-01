<?php

namespace App\Service\History\Configuration;

use App\Entity\EcuSwVersions;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Configuration\OdxCollection;
use App\Service\Configuration\Parameter;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistoryParameter implements HistoryParameterI
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
     * @var Parameter
     */
    private $parameterService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param Parameter              $parameterService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        Parameter  $parameterService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::SOFTWARE_MANAGEMENT);
        $this->parameterService = $parameterService;
    }

    /**
     * @param OdxCollection            $collection
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subConf
     * @param int                      $odx
     *
     * @throws \Exception
     */
    public function save(
        OdxCollection $collection,
        EcuSwVersions $sw,
        SubVehicleConfigurations $subConf,
        int $odx
    ): void
    {
        $mappingManager = $this->manager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class);

        $mappedSw = $mappingManager->findOneBy(['subVehicleConfiguration' => $subConf, 'ecuSwVersion' => $sw]);

        $beforeOdxCollection = $this->parameterService->getParameters(
            $odx, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw, $subConf
        );

        try {
            $this->parameterService->save($collection, $sw, $subConf, $odx);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterOdxCollection = $this->parameterService->getParameters(
            $odx, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw, $subConf
        );

        try {
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
            $this->history->setIsPrimary($mappedSw->getIsPrimarySw());
            $this->history->closeSession();
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}