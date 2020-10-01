<?php

namespace App\Service\History\Ecu\Sw;

use App\Entity\EcuSwVersions;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Header as HeaderModel;
use App\Service\Ecu\Sw\Header;
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
        $this->history = $history->getHistoryStrategy(HistoryTypes::ECU_PARAMETER_MANAGEMENT);
        $this->headerService = $headerService;
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param HeaderModel $header
     *
     * @throws \Exception
     */
    public function save(HeaderModel $header): void
    {
        $beforeHeader = $this->headerService->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()));

        try {
            $this->headerService->save($header);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader = $this->headerService->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()));

        try {
            $this->history->init();
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}