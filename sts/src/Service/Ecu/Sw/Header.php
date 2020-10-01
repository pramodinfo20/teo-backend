<?php

namespace App\Service\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSwVersionGeneralProperties;
use App\Entity\EcuSwVersions;
use App\Entity\ReleaseStatus;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\History\Strategies\HistoryStrategy;
use App\History\Strategies\HistoryStrategyFactory;
use App\Model\Header as HeaderModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class Header
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
     * Parameter constructor.
     *
     * @param ObjectManager                $manager
     * @param EntityManagerInterface       $entityManager
     * @param HistoryStrategyFactory $history
     * @throws \Exception
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager, HistoryStrategyFactory $history)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->history = $history->getHistoryStrategy(HistoryTypes::ECU_PARAMETER_MANAGEMENT);
    }

    /**
     * Retrieve parameters for header and set it in model
     *
     * @param EcuSwVersions $sw
     *
     * @return HeaderModel|null
     */
    public function getHeader(EcuSwVersions $sw): ?HeaderModel
    {
        $headerParameters = $this->manager
            ->getRepository(EcuSwVersions::class)
            ->getHeaderInformationBySwId($sw);

        $header = null;
        if (is_array($headerParameters)) {
            $header = new HeaderModel();
            $header->setEcuSwVersion($headerParameters['ecuSwVersionId']);
            $header->setStsVersion($headerParameters['stsPartNumber']);
            $header->setSwVersion($headerParameters['swVersion']);
            $header->setSubversionSuffix($headerParameters['suffixIfIsSubEcuSwVersion']);
            $header->setOdxSts02($headerParameters['odxSts02']);
            $header->setEcuId($headerParameters['ceEcuId']);

            $protocol = $this->manager
                ->getRepository(EcuCommunicationProtocols::class)
                ->find($headerParameters['ecuCommunicationProtocolId']);
            $header->setProtocol($protocol);

            $releaseStatus = $this->manager
                ->getRepository(ReleaseStatus::class)
                ->find($headerParameters['releaseStatusId']);
            $header->setStatus($releaseStatus);
            $header->setRequest(((!is_null($headerParameters['request'])) ? (($headerParameters['request'] != 'DUMMY_VALUE') ?
                substr($headerParameters['request'], 2) : null) : $headerParameters['request']));
            $header->setResponse(((!is_null($headerParameters['response'])) ? (($headerParameters['response'] != 'DUMMY_VALUE') ?
                substr($headerParameters['response'], 2) : null) : $headerParameters['response']));
            $header->setWindchillUrl($headerParameters['windchillLink']);
            $header->setInfo($headerParameters['information']);
            $header->setOdxVersion($headerParameters['odxVersion']);
            $header->setBigEndian($headerParameters['isBigEndian']);
            $header->setDiagnosticIdentifier($headerParameters['diagnosticIdentifier']);
        }


        return $header;
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
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            // activate logger
//            $this->manager->getConnection()
//                ->getConfiguration()
//                ->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());

            /* First save header basic data */
            $ecuSwVersion = $this->manager
                ->getRepository(EcuSwVersions::class)
                ->find($header->getEcuSwVersion());
            $ecuSwVersion->setSwVersion($header->getSwVersion());

            $ecu = $this->manager
                ->getRepository(ConfigurationEcus::class)
                ->find($header->getEcuId());
            $ecuSwVersion->setCeEcu($ecu);
            $ecuSwVersion->setSuffixIfIsSubEcuSwVersion($header->getSubversionSuffix());

            $releaseStatus = $this->manager
                ->getRepository(ReleaseStatus::class)
                ->find($header->getStatus());
            $ecuSwVersion->setReleaseStatus($releaseStatus);

            $protocol = $this->manager
                ->getRepository(EcuCommunicationProtocols::class)
                ->find($header->getProtocol());
            $ecuSwVersion->setEcuCommunicationProtocol($protocol);
            $ecuSwVersion->setOdxVersion($header->getOdxVersion());

            $this->entityManager->persist($ecuSwVersion);
            $this->entityManager->flush();

            /* Next save general properties for header */
            $ecuSwGeneralProperties = $this->manager
                ->getRepository(EcuSwVersionGeneralProperties::class)
                ->find($header->getEcuSwVersion());
            $ecuSwGeneralProperties->setInformation($header->getInfo());
            $ecuSwGeneralProperties->setUdsRequestId("0x" . $header->getRequest());
            $ecuSwGeneralProperties->setUdsResponseId("0x" . $header->getResponse());
            $ecuSwGeneralProperties->setWindchillLink($header->getWindchillUrl());
            $ecuSwGeneralProperties->setIsBigEndian($header->isBigEndian());
            $ecuSwGeneralProperties->setDiagnosticIdentifier($header->getDiagnosticIdentifier());

            $this->entityManager->persist($ecuSwGeneralProperties);
            $this->entityManager->flush();
            // disable logger
//            $this->manager->getConnection()
//                ->getConfiguration()
//                ->setSQLLogger(null);

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    /**
     * Save a non-entity data from form with history
     *
     * @param HeaderModel $header
     *
     * @throws \Exception
     */
    public function saveWithHistory(HeaderModel $header): void
    {
        $beforeHeader = $this->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()));

        try {
            $this->save($header);
        } catch(\Exception $exception) {
            throw $exception;
        }

        $afterHeader = $this->getHeader($this->manager
            ->getRepository(EcuSwVersions::class)
            ->find($header->getEcuSwVersion()));

        try {
            $this->history->save($beforeHeader, $afterHeader, HistoryEvents::UPDATE);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}