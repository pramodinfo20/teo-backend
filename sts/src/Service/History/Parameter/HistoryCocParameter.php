<?php


namespace App\Service\History\Parameter;


use App\Entity\CocParameterRelease;
use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Parameter\CocCollection;
use App\Service\Parameter\CocParameter;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistoryCocParameter implements HistoryCocParameterI
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
     * @var CocParameter
     */
    private $cocParameterService;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     * @param HistoryStrategyFactory $history
     * @param CocParameter           $cocParameterService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        CocParameter  $cocParameterService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::COC_VALUES_SETS_ASSIGNMENT);
        $this->cocParameterService = $cocParameterService;
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param CocCollection            $cocCollection
     * @param SubVehicleConfigurations $subVehicleConfigurations
     *
     * @throws \Exception
     */
    public function save(CocCollection $cocCollection, SubVehicleConfigurations $subVehicleConfigurations): void
    {
        $beforeCocCollection = $this->cocParameterService->getCocParameters($subVehicleConfigurations);

        try {
            $this->cocParameterService->save($cocCollection, $subVehicleConfigurations);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterCocCollection = $this->cocParameterService->getCocParameters($subVehicleConfigurations);

        try {
            $this->history->save($beforeCocCollection, $afterCocCollection, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Save  entity data from form with transactions
     *
     * @param CocParameterRelease $cocParameterRelease
     *
     * @throws \Exception
     */
    public function saveCoCReleased(CocParameterRelease $cocParameterRelease): void
    {
        $beforeCocParameter = $this->manager->getRepository(CocParameterRelease::class)
            ->find($cocParameterRelease->getCprSubVehicleConfiguration());

        try {
            $this->cocParameterService->saveCoCReleased($cocParameterRelease);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterCocParameter = $this->manager->getRepository(CocParameterRelease::class)
            ->find($cocParameterRelease->getCprSubVehicleConfiguration());


        if (is_null($beforeCocParameter)) {
            $beforeCocParameter = new CocParameterRelease();
        }

        try {
            $this->history->init();
            $this->history->save($beforeCocParameter, $afterCocParameter, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}