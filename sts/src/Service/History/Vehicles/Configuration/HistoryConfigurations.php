<?php

namespace App\Service\History\Vehicles\Configuration;

use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Model\Vehicles\Configuration\ConfigurationSearch;
use App\Service\Vehicles\Configuration\Configurations;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class HistoryConfigurations implements HistoryConfigurationsI
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
    private $historyVc;

    /**
     * @var HistoryStrategy
     */
    private $historySvc;

    /**
     * @var Configurations
     */
    private $configurationsService;

    /**
     * @var SubConfiguration
     */
    private $subConfigurationService;


    /**
     * Parameter constructor.
     *
     * @param ObjectManager            $manager
     * @param EntityManagerInterface   $entityManager
     * @param HistoryStrategyFactory   $history
     * @param Configurations           $configurationsService
     * @param SubConfiguration         $subConfigurationService
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager,
        HistoryStrategyFactory $history,
        Configurations  $configurationsService,
        SubConfiguration $subConfigurationService
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->historyVc = $history->getHistoryStrategy(HistoryTypes::VEHICLE_CONFIGURATION_VC_MANAGEMENT);
        $this->historySvc = $history->getHistoryStrategy(HistoryTypes::VEHICLE_CONFIGURATION_SVC_MANAGEMENT);
        $this->configurationsService = $configurationsService;
        $this->subConfigurationService = $subConfigurationService;
    }


    /**
     * @param ShortKeyModel         $shortKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixShortKey(
        ShortKeyModel $shortKeyModel,
        VehicleConfigurations $configuration
    ): ConfigurationSearch
    {
        $beforeConfiguration = new ShortKeyModel();

        try {
            $configurationSearch = $this->configurationsService->saveFixShortKey($shortKeyModel, $configuration);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);

        try {
            $this->historyVc->init();
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->saveFixedSubConfigurationsForConfiguration($configuration);
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param LongKeyModel          $longKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixLongKey(
        LongKeyModel $longKeyModel,
        VehicleConfigurations $configuration
    ): ConfigurationSearch
    {
        $beforeConfiguration = new LongKeyModel();

        try {
            $configurationSearch = $this->configurationsService->saveFixLongKey($longKeyModel, $configuration);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);

        try {
            $this->historyVc->init();
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->saveFixedSubConfigurationsForConfiguration($configuration);
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param ShortKeyModel         $shortKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionShortKey(
        ShortKeyModel $shortKeyModel,
        VehicleConfigurations $configuration
    ): ConfigurationSearch
    {
        $beforeConfiguration = $this->configurationsService->getModelToEdit($configuration);
        $beforeSubConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);
        $beforeSubConfigurationsTransformed = $this->transformSubConfigurationsArrayToModelsArray
        ($beforeSubConfigurations);

        try {
            $configurationSearch = $this->configurationsService->saveEditionShortKey($shortKeyModel, $configuration);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);
        $afterSubConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);
        $afterSubConfigurationsTransformed = $this->transformSubConfigurationsArrayToModelsArray
        ($afterSubConfigurations);

        try {
            $this->historyVc->init();
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::UPDATE);
            $this->saveEditedSubConfigurations($beforeSubConfigurationsTransformed, $afterSubConfigurationsTransformed);
            $this->historyVc->closeSession();
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param LongKeyModel          $longKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionLongKey(
        LongKeyModel $longKeyModel,
        VehicleConfigurations $configuration
    ): ConfigurationSearch
    {
        $beforeConfiguration = $this->configurationsService->getModelToEdit($configuration);
        $beforeSubConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);
        $beforeSubConfigurationsTransformed =
            $this->transformSubConfigurationsArrayToModelsArray($beforeSubConfigurations);

        try {
            $configurationSearch = $this->configurationsService->saveEditionLongKey($longKeyModel, $configuration);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);
        $afterSubConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);
        $afterSubConfigurationsTransformed = $this->transformSubConfigurationsArrayToModelsArray
        ($afterSubConfigurations);

        try {
            $this->historyVc->init();
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::UPDATE);
            $this->saveEditedSubConfigurations($beforeSubConfigurationsTransformed, $afterSubConfigurationsTransformed);
            $this->historyVc->closeSession();
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteConfiguration(VehicleConfigurations $configuration): bool
    {
        $beforeConfiguration = $this->configurationsService->getModelToEdit($configuration);
        $afterConfiguration = ($this->configurationsService->getConfigurationType($configuration) ==
            SubConfiguration::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();


        try {
            $this->historyVc->init();
            $this->historyVc->save($beforeConfiguration, $afterConfiguration,HistoryEvents::DELETE);
            $this->saveDeletedSubConfigurationsForConfiguration($configuration);
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        try {
            $success = $this->configurationsService->deleteConfiguration($configuration);
        } catch(\Exception $exception) {
            throw $exception;
        }

        return $success;
    }

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return void
     * @throws \Exception
     */
    private function saveFixedSubConfigurationsForConfiguration(VehicleConfigurations $configuration) : void
    {
        $subConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);

        try {
            foreach ($subConfigurations as $subConfiguration) {
                $beforeSubConfiguration = ($this->subConfigurationService->getConfigurationType($subConfiguration) ==
                    SubConfiguration::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();

                $this->historySvc->init(
                    $subConfiguration->getSubVehicleConfigurationId(),
                    $subConfiguration->getSubVehicleConfigurationName()
                );
                $this->historySvc->save($beforeSubConfiguration,
                $this->subConfigurationService->getSubConfiguration($subConfiguration), HistoryEvents::CREATE );
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return void
     * @throws \Exception
     */
    private function saveDeletedSubConfigurationsForConfiguration(VehicleConfigurations $configuration) : void
    {
        $subConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);

        try {
            foreach ($subConfigurations as $subConfiguration) {
                $afterSubConfiguration = ($this->subConfigurationService->getConfigurationType($subConfiguration) ==
                    SubConfiguration::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();

                $this->historySvc->init(
                    $subConfiguration->getSubVehicleConfigurationId(),
                    $subConfiguration->getSubVehicleConfigurationName()
                );
                $this->historySvc->save($this->subConfigurationService->getSubConfiguration($subConfiguration),
                    $afterSubConfiguration,HistoryEvents::DELETE);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param array $subConfigurations
     *
     * @return array
     */
    private function transformSubConfigurationsArrayToModelsArray(array $subConfigurations) : array
    {
        $subConfigurationsModels = [];

        foreach ($subConfigurations as $subConfiguration) {
            $subConfigurationsModels[] = [
                'model' => $this->subConfigurationService->getSubConfiguration($subConfiguration),
                'name' => $subConfiguration->getSubVehicleConfigurationName()
            ];
        }

        return $subConfigurationsModels;
    }


    /**
     * @param array $beforeSubConfigurations
     * @param array $afterSubConfigurations
     *
     * @return void
     * @throws \Exception
     */
    private function saveEditedSubConfigurations(array $beforeSubConfigurations, array $afterSubConfigurations) : void
    {
        try {
            foreach ($beforeSubConfigurations as $index => $beforeSubConfiguration) {
                $this->historySvc->init(
                    $beforeSubConfiguration['model']->getSubConfigurationId(),
                    $beforeSubConfiguration['name']
                );
                $this->historySvc->save($beforeSubConfiguration['model'], $afterSubConfigurations[$index]['model'],
                    HistoryEvents::UPDATE);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}