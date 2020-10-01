<?php

namespace App\Service\History\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwParameters;
use App\Entity\EcuSwVersions;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\OdxCollection;
use App\Service\Ecu\Sw\Header;
use App\Service\Ecu\Sw\Parameter;
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
     * @var Header
     */
    private $headerService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param Parameter              $parameterService
     * @param Header                 $headerService
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        Parameter  $parameterService,
        Header $headerService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::ECU_PARAMETER_MANAGEMENT);
        $this->parameterService = $parameterService;
        $this->headerService = $headerService;
    }

    /**
     * Save a non-entity data from form with history
     *
     * @param OdxCollection     $collection
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param array             $parametersBag
     * @param int               $odx
     *
     * @throws \Exception
     */
    public function save(
        OdxCollection $collection, ConfigurationEcus $ecu, EcuSwVersions $sw, array $parametersBag, int $odx
    ): void
    {
        $beforeOdxCollection = $this->parameterService->getParameters(
            $odx, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->parameterService->save($collection, $ecu, $sw, $parametersBag, $odx);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterOdxCollection = $this->parameterService->getParameters(
            $odx, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Change parameters order with array
     *
     * @param EcuSwVersions $sw
     * @param array         $orders
     *
     * @return void
     * @throws \Exception
     */
    public function changeOrders(EcuSwVersions $sw, array $orders): void
    {
        $beforeHeader =  $this->headerService->getHeader($sw);
        $beforeOdxCollection = $this->parameterService->getParameters(
            2, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->parameterService->changeOrders($sw, $orders);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($sw);
        $afterOdxCollection = $this->parameterService->getParameters(
            2, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->history->init();
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Save cloned Parameters for Sw
     *
     * @param EcuSwVersions $sw
     * @param array         $form
     *
     * @return void
     * @throws \Exception
     */
    public function saveClonedParameterForSwId(EcuSwVersions $sw, array $form): void
    {
        $beforeHeader =  $this->headerService->getHeader($sw);
        $beforeOdxCollection = $this->parameterService->getParameters(
            2, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->parameterService->saveClonedParameterForSwId($sw, $form);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($sw);
        $afterOdxCollection = $this->parameterService->getParameters(
            2, $sw->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $sw
        );

        try {
            $this->history->init();
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Resolve conflicts for selected parameters and sw
     *
     * @param EcuSwVersions   $swDestination
     * @param array           $parametersConflict
     * @param array           $parametersWConflict
     *
     * @return string
     * @throws \Exception
     */
    public function resolveConflicts(
        EcuSwVersions $swDestination,
        array $parametersConflict,
        array $parametersWConflict
    ): string
    {
        $beforeHeader =  $this->headerService->getHeader($swDestination);
        $beforeOdxCollection = $this->parameterService->getParameters(
            2, $swDestination->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $swDestination
        );

        try {
            $swName = $this->parameterService->resolveConflicts(
                $swDestination, $parametersConflict, $parametersWConflict);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($swDestination);
        $afterOdxCollection = $this->parameterService->getParameters(
            2, $swDestination->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $swDestination
        );

        try {
            $this->history->init($swDestination->getEcuSwVersionId());
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $swName;
    }

    /**
     * Copy parameter to other sw without conflict
     *
     * @param EcuSwVersions $swDestination
     * @param array         $parametersWConflict
     *
     * @return string
     * @throws \Exception
     */
    public function copyWithoutConflict(EcuSwVersions $swDestination, array $parametersWConflict): string
    {
        $beforeHeader =  $this->headerService->getHeader($swDestination);
        $beforeOdxCollection = $this->parameterService->getParameters(
            2, $swDestination->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $swDestination
        );

        try {
            $swName = $this->parameterService->copyWithoutConflict($swDestination, $parametersWConflict);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader =  $this->headerService->getHeader($swDestination);
        $afterOdxCollection = $this->parameterService->getParameters(
            2, $swDestination->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName(), $swDestination
        );

        try {
            $this->history->init($swDestination->getEcuSwVersionId());
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
            $this->history->save($beforeOdxCollection, $afterOdxCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $swName;
    }
}