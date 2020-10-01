<?php


namespace App\History\Strategies;


use App\Entity\HistoryEvents;
use App\Entity\HistoryVcManagementSvc;
use App\Entity\HistoryVcManagementVc;
use App\Entity\Users;
use App\Entity\VehicleConfigurationProperties;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use App\Entity\VehicleConfigurations;
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

class VehicleConfigurationVcManagementStrategy extends HistoryStrategy
{
    use VehicleConfigurationManagementStrategyTrait;

    public function init(int $fk = null, string $name = null): void
    {
        try {
            if (!isset($_SESSION['tmp_history'])) {
                throw new \Exception('History - save method. Session does not exist!');
            } else {
                $fkObject = $this->manager
                    ->getRepository(VehicleConfigurations::class)->find($fk ?? $_SESSION['tmp_history']['fk']);
                $history = new HistoryVcManagementVc();
                $history->setCreatedAt(new \DateTime(date($_SESSION['tmp_history']['created_at'])));
                $history->setCreatedBy($this->manager->getRepository(Users::class)->find($_SESSION['sts_userid']));
                $history->setName($name ?? ((is_null($fkObject->getVehicleCustomerKey())) ?
                        $fkObject->getVehicleConfigurationKey() :
                        $fkObject->getVehicleConfigurationKey().
                        "  " . $fkObject->getVehicleCustomerKey()));
                $history->setComment($_SESSION['tmp_history']['comment']);
                $history->setFkId($fk ?? $_SESSION['tmp_history']['fk']);

                $this->manager->persist($history);
                $this->manager->flush();

                $_SESSION['tmp_history']['id'] = $history->getHId();
                $_SESSION['tmp_history']['historical_table'] = $this->getTableName();

                $_SESSION['tmp_history']['fk_vc_id'] = $fkObject->getVehicleConfigurationId();
                $_SESSION['tmp_history']['history_vc_fk_id'] = $history->getHId();
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

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
                    $history = $this->manager->getRepository(HistoryVcManagementVc::class)
                        ->find($_SESSION['tmp_history']['id']);
                    $history->setBeforeVcModel(base64_encode(serialize($beforeInterface)));
                    $history->setAfterVcModel(base64_encode(serialize($afterInterface)));
                    $history->setEvent($this->manager->getRepository(HistoryEvents::class)->find($event));

                    if ($beforeInterface instanceof LongKeyModel) {
                        $history->setKeyType(SubConfiguration::LONG_KEY);
                    } else {
                        $history->setKeyType(SubConfiguration::SHORT_KEY);
                    }

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
            $history = $this->manager->getRepository(HistoryVcManagementVc::class)->find($id);

            $historySubConfigurations = $this->manager->getRepository(HistoryVcManagementSvc::class)
                ->findBy(['historyVcFkId' => $id]);

            $reflectionClass = ($history->getKeyType() == SubConfiguration::SHORT_KEY) ?
                ShortKeyModel::class : LongKeyModel::class;

            $configurationAllowedClasses = ['allowedClasses' => [LongKeyModel::class, ShortKeyModel::class]];

            $configuration = $this->getHistoryModel(unserialize(base64_decode($history->getBeforeVcModel()),
                $configurationAllowedClasses),
                unserialize(base64_decode($history->getAfterVcModel()), $configurationAllowedClasses),
                new \ReflectionClass($reflectionClass), $history->getEvent()->getHeId());


            $historyMenu = null;

            $subConfigurations = [];
            if (!empty($historySubConfigurations)) {

                 foreach ($historySubConfigurations as $hSubConfiguration) {
                     $subConfiguration = new HistorySubConfiguration();
                     $subConfiguration->setSubConfigurationId($hSubConfiguration->getHId())
                         ->setSubConfigurationName($hSubConfiguration->getName());
                     $subConfigurations[] = $subConfiguration;
                 }

                 $historyMenu = new HistoryConfigurationMenu();
                 $historyMenu->setHistoryConfigurationId($history->getHId())
                     ->setHistoryConfigurationName($history->getName())
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
        $result = $this->manager->getRepository(HistoryVcManagementVc::class)->getMetaDataById($id);

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
        return HistoryVcManagementVc::class;
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