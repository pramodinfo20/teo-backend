<?php

namespace App\Service\History\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Configuration\Header as HeaderModel;
use App\Service\Configuration\Header;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistoryHeader implements HistoryHeaderI
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
     * @var Header
     */
    private $headerService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param Header                 $headerService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        Header  $headerService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::SOFTWARE_MANAGEMENT);
        $this->headerService = $headerService;
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param HeaderModel              $header
     * @param ConfigurationEcus        $ecu
     * @param SubVehicleConfigurations $subConf
     *
     * @throws \Exception
     */
    public function save(HeaderModel $header, ConfigurationEcus $ecu, SubVehicleConfigurations $subConf): void
    {
        $beforeHeader = $this->headerService->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()), $subConf);

        try {
            $this->headerService->save($header, $ecu, $subConf);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader = $this->headerService->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()), $subConf);

        $sw = $this->manager->getRepository(EcuSwVersions::class)->find($header->getEcuSwVersion());

        try {
            $this->history->init(
                $subConf->getSubVehicleConfigurationId(),
                $this->history->prepareName($sw, $subConf)
        );
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}