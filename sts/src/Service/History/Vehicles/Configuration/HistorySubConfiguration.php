<?php

namespace App\Service\History\Vehicles\Configuration;

use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
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

class HistorySubConfiguration implements HistorySubConfigurationI
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
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch
    {
        try {
            $configurationSearch = $this->subConfigurationService->saveShortKey($shortKeyModel);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($configurationSearch->configurationId);

        $configuration = $subConfiguration->getVehicleConfiguration();

        $subConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);

        $beforeConfiguration =  new ShortKeyModel();

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);

        $beforeSubConfiguration = new ShortKeyModel();
        $afterSubConfiguration =  $this->subConfigurationService->getSubConfiguration($subConfiguration);

        if (count($subConfigurations) > 1) {
            $this->historySvc->init(
                $subConfiguration->getSubVehicleConfigurationId(),
                $subConfiguration->getSubVehicleConfigurationName()
            );
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::CREATE);
            $this->historyVc->closeSession();
        } else {
            $this->historyVc->init($configuration->getVehicleConfigurationId(),
                (!is_null($configuration->getVehicleCustomerKey()) ?
                    $configuration->getVehicleConfigurationKey() . " " .
                    $configuration->getVehicleCustomerKey() :
                    $configuration->getVehicleConfigurationKey()));
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->historySvc->init($subConfiguration->getSubVehicleConfigurationId(),
                $subConfiguration->getSubVehicleConfigurationName());
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::CREATE);
            $this->historyVc->closeSession();
        }

          return $configurationSearch;
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveLongKey(LongKeyModel $longKeyModel): ConfigurationSearch
    {
        try {
            $configurationSearch = $this->subConfigurationService->saveLongKey($longKeyModel);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($configurationSearch->configurationId);

        $configuration = $subConfiguration->getVehicleConfiguration();

        $subConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $configuration]);

        $beforeConfiguration =  new LongKeyModel();

        $afterConfiguration = $this->configurationsService->getModelToEdit($configuration);

        $beforeSubConfiguration = new LongKeyModel();
        $afterSubConfiguration =  $this->subConfigurationService->getSubConfiguration($subConfiguration);

        if (count($subConfigurations) > 1) {
            $this->historySvc->init(
                $subConfiguration->getSubVehicleConfigurationId(),
                $subConfiguration->getSubVehicleConfigurationName()
            );
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::CREATE);
            $this->historyVc->closeSession();
        } else {
            $this->historyVc->init($configuration->getVehicleConfigurationId(),
                (!is_null($configuration->getVehicleCustomerKey()) ?
                    $configuration->getVehicleConfigurationKey() . " " .
                    $configuration->getVehicleCustomerKey() :
                    $configuration->getVehicleConfigurationKey()));
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->historySvc->init(
                $subConfiguration->getSubVehicleConfigurationId(),
                $subConfiguration->getSubVehicleConfigurationName()
            );
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::CREATE);
            $this->historyVc->closeSession();
        }

        return $configurationSearch;
    }

    /**
     * @param ShortKeyModel $shortKeyModel
     * @param Users         $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionShortKey(ShortKeyModel $shortKeyModel, Users $user): ConfigurationSearch
    {
        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($shortKeyModel->getSubConfigurationId());

        $beforeSubConfiguration = $this->subConfigurationService->getSubConfiguration($subConfiguration);

        try {
            $configurationSearch = $this->subConfigurationService->saveEditionShortKey($shortKeyModel, $user);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $afterSubConfiguration = $this->subConfigurationService->getSubConfiguration($subConfiguration);
        try {
            $this->historySvc->init();
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::UPDATE);
            $this->historySvc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param LongKeyModel $longKeyModel
     * @param Users        $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionLongKey(LongKeyModel $longKeyModel, Users $user): ConfigurationSearch
    {
        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($longKeyModel->getSubConfigurationId());

        $beforeSubConfiguration = $this->subConfigurationService->getSubConfiguration($subConfiguration);

        try {
            $configurationSearch = $this->subConfigurationService->saveEditionLongKey($longKeyModel, $user);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $afterSubConfiguration = $this->subConfigurationService->getSubConfiguration($subConfiguration);
        try {
            $this->historySvc->init();
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::UPDATE);
            $this->historySvc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch
    {
        $beforeConfiguration = new ShortKeyModel();

        try {
            $configurationSearch = $this->subConfigurationService->saveFixShortKey($shortKeyModel);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($shortKeyModel->getSubConfigurationId());

        $configuration = $subConfiguration->getVehicleConfiguration();

        $afterConfiguration = $this->configurationsService->getModelToEdit($subConfiguration->getVehicleConfiguration());

        try {
            $this->historyVc->init($configuration->getVehicleConfigurationId(),
                (!is_null($configuration->getVehicleCustomerKey()) ?
                    $configuration->getVehicleConfigurationKey() . " " .
                    $configuration->getVehicleCustomerKey() :
                    $configuration->getVehicleConfigurationKey()));
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->saveFixedSubConfigurationsForConfiguration($subConfiguration->getVehicleConfiguration());
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixLongKey(LongKeyModel $longKeyModel): ConfigurationSearch
    {
        $beforeConfiguration = new LongKeyModel();

        try {
            $configurationSearch = $this->subConfigurationService->saveFixLongKey($longKeyModel);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->find($longKeyModel->getSubConfigurationId());

        $configuration = $subConfiguration->getVehicleConfiguration();

        $afterConfiguration = $this->configurationsService->getModelToEdit($subConfiguration->getVehicleConfiguration());

        try {
            $this->historyVc->init($configuration->getVehicleConfigurationId(),
                (!is_null($configuration->getVehicleCustomerKey()) ?
                    $configuration->getVehicleConfigurationKey() . " " .
                    $configuration->getVehicleCustomerKey() :
                    $configuration->getVehicleConfigurationKey()));
            $this->historyVc->save($beforeConfiguration, $afterConfiguration, HistoryEvents::CREATE);
            $this->saveFixedSubConfigurationsForConfiguration($subConfiguration->getVehicleConfiguration());
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $configurationSearch;
    }

    /**
     * @param SubVehicleConfigurations $subconfiguration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteSubConfiguration(SubVehicleConfigurations $subconfiguration): bool
    {
        $subConfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findBy(['vehicleConfiguration' => $subconfiguration->getVehicleConfiguration()]);

        $beforeSubConfiguration = $this->subConfigurationService->getSubConfiguration($subconfiguration);
        $afterSubConfiguration =  ($this->subConfigurationService
                ->getConfigurationType($subconfiguration) == SubConfiguration::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();

        $beforeConfiguration = $this->configurationsService
            ->getModelToEdit($subconfiguration->getVehicleConfiguration());
        $afterConfiguration = ($this->configurationsService
                ->getConfigurationType($subconfiguration->getVehicleConfiguration()) ==
            SubConfiguration::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();

        try {
            $this->historyVc->init($subconfiguration->getVehicleConfiguration()->getVehicleConfigurationId());
            $this->historyVc->save($beforeConfiguration, $afterConfiguration,HistoryEvents::DELETE);
            $this->historySvc->init($subconfiguration->getSubVehicleConfigurationId());
            $this->historySvc->save($beforeSubConfiguration, $afterSubConfiguration, HistoryEvents::DELETE);
            $this->historyVc->closeSession();
        } catch (\Exception $exception) {
            throw $exception;
        }

        try {
            $success = $this->subConfigurationService->deleteSubConfiguration($subconfiguration);
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
}