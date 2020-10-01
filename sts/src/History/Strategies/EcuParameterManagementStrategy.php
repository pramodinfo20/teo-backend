<?php

namespace App\History\Strategies;

use App\Entity\EcuSwVersions;
use App\Entity\HistoryEcuParameterManagement;
use App\Entity\HistoryEvents;
use App\Entity\Users;
use App\Enum\LegacyActions;
use App\Model\ConvertibleToHistoryI;
use App\Model\Header;
use App\Model\History\HistoryMetaData;
use App\Model\History\HistorySw;
use App\Model\History\HistoryI;
use App\Model\Odx1Collection;
use App\Model\Odx2Collection;
use App\Model\OdxCollection;

class EcuParameterManagementStrategy extends HistoryStrategy
{
    public function init(int $fk = null, string $name = null) : void
    {
        try {
            if (!isset($_SESSION['tmp_history'])) {
                throw new \Exception('History - save method. Session does not exist!');
            } else {
                $fkObject = $this->manager
                    ->getRepository(EcuSwVersions::class)->find($fk ?? $_SESSION['tmp_history']['fk']);
                $history = new HistoryEcuParameterManagement();
                $history->setCreatedAt(new \DateTime(date($_SESSION['tmp_history']['created_at'])));
                $history->setCreatedBy($this->manager->getRepository(Users::class)->find($_SESSION['sts_userid']));
                $history->setName($name ?? ((is_null($fkObject->getSuffixIfIsSubEcuSwVersion())) ?
                $fkObject->getSwVersion() :
                        $fkObject->getSwVersion(). " ---> " . $fkObject->getSuffixIfIsSubEcuSwVersion()));
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

    public function save(
        ConvertibleToHistoryI $beforeInterface,
        ConvertibleToHistoryI $afterInterface,
        int $event
    ): void
    {
        try {
            if ($beforeInterface instanceof Header && $afterInterface instanceof Header) {
                if (!isset($_SESSION['tmp_history'])) {
                    throw new \Exception('History - save method. Session does not exist!');
                } else {
                    $history = $this->manager->getRepository(HistoryEcuParameterManagement::class)
                        ->find($_SESSION['tmp_history']['id']);
                    $history->setBeforeHeaderModel(base64_encode(serialize($beforeInterface)));
                    $history->setAfterHeaderModel(base64_encode(serialize($afterInterface)));
                    $history->setEvent($this->manager->getRepository(HistoryEvents::class)->find($event));

                    $this->manager->persist($history);
                    $this->manager->flush();
                }
            } else if ($beforeInterface instanceof OdxCollection && $afterInterface instanceof
                OdxCollection) {
                if (!isset($_SESSION['tmp_history'])) {
                    throw new \Exception('History - save method. Session does not exist!');
                } else {
                    $history = $this->manager->getRepository(HistoryEcuParameterManagement::class)
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

                        unset($_SESSION['tmp_history']);
                    }
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
            $history = $this->manager->getRepository(HistoryEcuParameterManagement::class)->find($id);

            $headerAllowedClasses = ['allowedClasses' => [Header::class]];
            $header = $this->getHistoryModel(unserialize(base64_decode($history->getBeforeHeaderModel()),
                $headerAllowedClasses),
                unserialize(base64_decode($history->getAfterHeaderModel()), $headerAllowedClasses),
                new \ReflectionClass(Header::class), $history->getEvent()->getHeId());


            $collectionAllowedClasses = ['allowedClasses' => [Odx1Collection::class, Odx2Collection::class]];

            $reflectionClass = ($history->getOdxVersion() == 1) ? Odx1Collection::class : Odx2Collection::class;


            $collection = $this->getHistoryCollectionModel(unserialize(base64_decode($history->getBeforeCollectionModel()),
                $collectionAllowedClasses), unserialize(base64_decode($history->getAfterCollectionModel()),
                $collectionAllowedClasses),
                new \ReflectionClass($reflectionClass), $history->getEvent()->getHeId());


            $sw = new HistorySw();
            $sw->setHistoryHeader($header);
            $sw->setHistoryOdxCollection($collection);
            $sw->setOdx($history->getOdxVersion());

            return $sw;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getTableName(): string
    {
        return HistoryEcuParameterManagement::class;
    }

    public function getMetaData(int $id): HistoryMetaData
    {
        $result = $this->manager->getRepository(HistoryEcuParameterManagement::class)->getMetaDataById($id);

        $metaData = new HistoryMetaData();
        $metaData->setUser($result->getCreatedBy())
            ->setDateTime($result->getCreatedAt())
            ->setHistoryEvent($result->getEvent())
            ->setName($result->getName())
            ->setComment($result->getComment());

        return $metaData;
    }

    public function getLegacyAction() : string
    {
        return LegacyActions::PARAMETER_MANAGEMENT;
    }

    public function isOnlyLog(): bool
    {
        return false;
    }
}