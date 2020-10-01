<?php

namespace App\History\Strategies;

use App\Entity\EcuSwVersions;
use App\Entity\HistoryEvents;
use App\Entity\HistorySoftwareManagement;
use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Enum\LegacyActions;
use App\Model\Configuration\Header;
use App\Model\Configuration\Odx1Collection;
use App\Model\Configuration\OdxCollection;
use App\Model\ConvertibleToHistoryI;
use App\Model\History\HistoryI;
use App\Model\History\HistoryMetaData;

class SoftwareManagementStrategy extends HistoryStrategy
{
    public function init(int $fk = null, string $name = null): void
    {
        try {
            if (!isset($_SESSION['tmp_history'])) {
                throw new \Exception('History - save method. Session does not exist!');
            } else {
                $fkObject = $this->manager
                    ->getRepository(SubVehicleConfigurations::class)->find($fk ?? $_SESSION['tmp_history']['fk']);
                $history = new HistorySoftwareManagement();
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

    public function save(ConvertibleToHistoryI $beforeInterface, ConvertibleToHistoryI $afterInterface, int $event): void
    {
        try {
            if ($beforeInterface instanceof Header && $afterInterface instanceof Header) {
                if (!isset($_SESSION['tmp_history'])) {
                    throw new \Exception('History - save method. Session does not exist!');
                } else {
                    $history = $this->manager->getRepository(HistorySoftwareManagement::class)
                        ->find($_SESSION['tmp_history']['id']);
                    $history->setBeforeHeaderModel(base64_encode(serialize($beforeInterface)));
                    $history->setAfterHeaderModel(base64_encode(serialize($afterInterface)));
                    $history->setEvent($this->manager->getRepository(HistoryEvents::class)->find($event));

                    $this->manager->persist($history);
                    $this->manager->flush();
                }
            } else if ($beforeInterface instanceof OdxCollection
                && $afterInterface instanceof OdxCollection) {
                if (!isset($_SESSION['tmp_history'])) {
                    throw new \Exception('History - save method. Session does not exist!');
                } else {
                    $history = $this->manager->getRepository(HistorySoftwareManagement::class)
                        ->find($_SESSION['tmp_history']['id']);

                    if (is_null($history)) {
                        throw new \Exception('History - save method. Historical entry does not exist!');
                    } else {
                        $history->setBeforeCollectionModel(base64_encode(serialize($beforeInterface)));
                        $history->setAfterCollectionModel(base64_encode(serialize($afterInterface)));

                        $odxVersion = ($beforeInterface instanceof Odx1Collection)? 1 : 2;
                        $history->setOdxVersion($odxVersion);

                        $this->manager->persist($history);
                        $this->manager->flush();
                    }
                }

            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function load(int $id): HistoryI
    {
        // TODO: Implement load() method.
    }

    public function getMetaData(int $id): HistoryMetaData
    {
        $result = $this->manager->getRepository(HistorySoftwareManagement::class)->getMetaDataById($id);

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
        return HistorySoftwareManagement::class;
    }

    public function getLegacyAction(): string
    {
        return LegacyActions::SOFTWARE_MANAGEMENT;
    }

    public function isOnlyLog(): bool
    {
        return true;
    }

    public function prepareName(EcuSwVersions $sw, SubVehicleConfigurations $subconfiguration) : string
    {
        $name = (is_null($subconfiguration->getVehicleConfiguration()->getVehicleCustomerKey())) ?
            $subconfiguration->getSubVehicleConfigurationName() :
            $subconfiguration->getSubVehicleConfigurationName().
            "  " . $subconfiguration->getVehicleConfiguration()->getVehicleCustomerKey();

        $name .= (!is_null($sw->getSuffixIfIsSubEcuSwVersion())) ?
            $sw->getSwVersion()." ---> ".$sw->getSuffixIfIsSubEcuSwVersion() : $sw->getSwVersion();

        return $name;
    }

    public function setIsPrimary(bool $isPrimary) : void
    {
        if (!isset($_SESSION['tmp_history'])) {
            throw new \Exception('History - save method. Session does not exist!');
        } else {
            $history = $this->manager->getRepository(HistorySoftwareManagement::class)
                ->find($_SESSION['tmp_history']['id']);

            $history->setIsPrimarySw($isPrimary);

            $this->manager->persist($history);
            $this->manager->flush();

            unset($_SESSION['tmp_hisotry']);
        }
    }
}