<?php


namespace App\History\Strategies;


use App\Entity\HistoryEvents;
use App\Entity\HistoryVcManagementSvc;
use App\Entity\HistoryVcManagementVc;
use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Entity\VehicleConfigurationProperties;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use App\Enum\LegacyActions;
use App\Model\ConfigurationI;
use App\Model\ConvertibleToHistoryI;
use App\Model\History\HistoryConfiguration;
use App\Model\History\HistoryConfigurationMenu;
use App\Model\History\HistoryI;
use App\Model\History\HistoryMetaData;
use App\Model\History\HistorySubConfiguration;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Collections\ArrayCollection;

class VehicleConfigurationSvcManagementStrategy extends HistoryStrategy
{
    use VehicleConfigurationManagementStrategyTrait;

    public function init(int $fk = null, string $name = null): void
    {
        try {
            if (!isset($_SESSION['tmp_history'])) {
                throw new \Exception('History - save method. Session does not exist!');
            } else {
                $fkObject = $this->manager
                    ->getRepository(SubVehicleConfigurations::class)->find($fk ?? $_SESSION['tmp_history']['fk']);
                $history = new HistoryVcManagementSvc();
                $history->setCreatedAt(new \DateTime(date($_SESSION['tmp_history']['created_at'])));
                $history->setCreatedBy($this->manager->getRepository(Users::class)->find($_SESSION['sts_userid']));
                $history->setName($name ?? ((is_null($fkObject->getVehicleConfiguration()->getVehicleCustomerKey())) ?
                        $fkObject->getSubVehicleConfigurationName() :
                        $fkObject->getSubVehicleConfigurationName().
                        "  " . $fkObject->getVehicleConfiguration()->getVehicleCustomerKey()));
                $history->setComment($_SESSION['tmp_history']['comment']);
                $history->setFkId($fk ?? $_SESSION['tmp_history']['fk']);


                $this->manager->persist($history);
                $this->manager->flush();

                $_SESSION['tmp_history']['id'] = $history->getHId();
                $_SESSION['tmp_history']['historical_table'] = $this->getTableName();
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * IMPORTANT! NULL - fk_vc_id & history_vc_fk_id - EVENTS: SUBCONFIGURATION 1.update 2.delete 3.create/fix(same key)
     *            NOT NULL - fk_vc_id & history_vc_fk_id - EVENTS: CONFIGURATION 1. create/fix 2.update 3.delete
     * @param ConvertibleToHistoryI $beforeInterface
     * @param ConvertibleToHistoryI $afterInterface
     * @param int                   $event
     *
     * @throws \Exception
     */
    public function save(
        ConvertibleToHistoryI $beforeInterface,
        ConvertibleToHistoryI $afterInterface,
        int $event
    ): void
    {
        try {
            if ($beforeInterface instanceof ConfigurationI && $afterInterface instanceof ConfigurationI) {
                if (!isset($_SESSION['tmp_history'])) {
                    throw new \Exception('History - save method. Session does not exist!');
                } else {
                    $history = $this->manager->getRepository(HistoryVcManagementSvc::class)
                        ->find($_SESSION['tmp_history']['id']);
                    $history->setBeforeSvcModel(base64_encode(serialize($beforeInterface)));
                    $history->setAfterSvcModel(base64_encode(serialize($afterInterface)));
                    $history->setEvent($this->manager->getRepository(HistoryEvents::class)->find($event));

                    if ($beforeInterface instanceof LongKeyModel) {
                        $history->setKeyType(SubConfiguration::LONG_KEY);
                    } else {
                        $history->setKeyType(SubConfiguration::SHORT_KEY);
                    }

                    $history->setFkVcId((isset($_SESSION['tmp_history']['fk_vc_id'])) ?
                        $_SESSION['tmp_history']['fk_vc_id'] : null);
                    $history->setHistoryVcFkId((isset($_SESSION['tmp_history']['history_vc_fk_id'])) ?
                        $_SESSION['tmp_history']['history_vc_fk_id'] : null);

                    $this->manager->persist($history);
                    $this->manager->flush();
                }
            } else {
                throw new \Exception('History - save method. Convertible to history error!');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function load(int $id): HistoryI
    {
        try {
            $history = $this->manager->getRepository(HistoryVcManagementSvc::class)->find($id);

            if (is_null($history->getHistoryVcFkId())) {
                $historyConfiguration = null;
                $historySubConfigurations = [];
            } else {
                $historyConfiguration = $this->manager->getRepository(HistoryVcManagementVc::class)
                    ->find($history->getHistoryVcFkId());
                $historySubConfigurations = $this->manager->getRepository(HistoryVcManagementSvc::class)
                    ->findBy(['historyVcFkId' => $historyConfiguration]);
            }



            $reflectionClass = ($history->getKeyType() == SubConfiguration::SHORT_KEY) ?
                ShortKeyModel::class : LongKeyModel::class;

            $configurationAllowedClasses = ['allowedClasses' => [LongKeyModel::class, ShortKeyModel::class]];

            $configuration = $this->getHistoryModel(unserialize(base64_decode($history->getBeforeSvcModel()),
                $configurationAllowedClasses),
                unserialize(base64_decode($history->getAfterSvcModel()), $configurationAllowedClasses),
                new \ReflectionClass($reflectionClass), $history->getEvent()->getHeId());

            $subConfigurations = null;
            $historyMenu = null;

            if (!empty($historySubConfigurations)) {
                $subConfigurations = [];

                foreach ($historySubConfigurations as $hSubConfiguration) {
                    $subConfiguration = new HistorySubConfiguration();
                    $subConfiguration->setSubConfigurationId($hSubConfiguration->getHId())
                        ->setSubConfigurationName($hSubConfiguration->getName());
                    $subConfigurations[] = $subConfiguration;
                }

                $historyMenu = new HistoryConfigurationMenu();
                $historyMenu->setHistoryConfigurationId($historyConfiguration->getHId())
                    ->setHistoryConfigurationName($historyConfiguration->getName())
                    ->setHistorySubConfigurations($subConfigurations);
            }

            $historyConfiguration = new HistoryConfiguration();
            $historyConfiguration->setKeyType($history->getKeyType())
                ->setHistoryConfiguration($configuration)
                ->setHistoryConfigurationMenu($historyMenu)
                ->setKeyOptions($this->getAllOptions())
                ->setStsProductionLocations($this->getStsProductionLocations())
                ->setConfigurationColors($this->getConfigurationColors())
                ->setReleaseStates($this->getReleaseStates())
                ->setUsers($this->getUserNameAndSurname(
                    $configuration->getReleasedByUser()->getBeforeValue(),
                    $configuration->getReleasedByUser()->getAfterValue()
                ))
                ->setEcus($this->getAllEcus());

            return $historyConfiguration;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getMetaData(int $id): HistoryMetaData
    {
        $result = $this->manager->getRepository(HistoryVcManagementSvc::class)->getMetaDataById($id);

        $metaData = new HistoryMetaData();
        $metaData->setUser($result->getCreatedBy())
            ->setDateTime($result->getCreatedAt())
            ->setHistoryEvent($result->getEvent())
            ->setName($result->getName())
            ->setComment($result->getComment());

        return $metaData;
    }

    public function getTableName(): string
    {
        return HistoryVcManagementSvc::class;
    }

    public function getLegacyAction(): string
    {
        return LegacyActions::VEHICLE_CONFIGURATION;
    }

    protected function _getManager()
    {
        return $this->manager;
    }


    public function isOnlyLog(): bool
    {
        return true;
    }
}