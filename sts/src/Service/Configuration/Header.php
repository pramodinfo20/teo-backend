<?php

namespace App\Service\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSubConfigurationVehicleContainment;
use App\Entity\EcuSwParameterEcuSwVersionMappingOverwrite;
use App\Entity\EcuSwVersions;
use App\Entity\PentaVariants;
use App\Entity\ReleaseStatus;
use App\Entity\SubVehicleConfigurations;
use App\Model\Configuration\Header as HeaderModel;
use App\Service\History\Configuration\HistoryHeaderI;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class Header implements HistoryHeaderI
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
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieve parameters for header and set it in model
     *
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subconf
     *
     * @return HeaderModel
     */
    public function getHeader(EcuSwVersions $sw, SubVehicleConfigurations $subconf): HeaderModel
    {
        $headerParameters = $this->manager
            ->getRepository(EcuSwVersions::class)
            ->getHeaderInformationBySwId($sw);
        $penta = $this->manager
            ->getRepository(PentaVariants::class)
            ->findOneBy(['subVehicleConfiguration' => $subconf->getSubVehicleConfigurationId()]);

        $subconfContainment = $this->manager
            ->getRepository(EcuSubConfigurationVehicleContainment::class)
            ->findOneBy(['ceEcu' => $sw->getCeEcu()->getCeEcuId(), 'subVehicleConfiguration' => $subconf->getSubVehicleConfigurationId()]);

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
            $header->setClonedFrom($this->getCloneSource($subconf, $sw));
            $header->setConfiguration($subconf->getSubVehicleConfigurationName());
            $header->setPenta($penta->getPentaVariantName());
            $header->setEcuName($sw->getCeEcu()->getEcuName());
            $header->setOdxSourceType($subconfContainment->getOdxSourceType());
            $header->setOdxSourceTypeName($subconfContainment->getOdxSourceType()->getOdxSourceTypeName());
        }


        return $header;
    }

    /**
     * Check if source have same parameters values
     *
     * @param SubVehicleConfigurations $subconf
     * @param EcuSwVersions            $sw
     *
     * @return string|null
     */
    public function getCloneSource(SubVehicleConfigurations $subconf, EcuSwVersions $sw): ?string
    {
        if (!is_null($subconf->getSourceSubVehicleConfiguration())) {
            $overwriteManager = $this->manager->getRepository(EcuSwParameterEcuSwVersionMappingOverwrite::class);

            $overwriteBase = $overwriteManager->findBy(['subVehicleConfiguration' => $subconf, 'ecuSwVersion' => $sw]);
            $overwriteSource = $overwriteManager->findBy(['subVehicleConfiguration' => $subconf->getSourceSubVehicleConfiguration(),
                'ecuSwVersion' => $sw]);
            if (count($overwriteBase) == count($overwriteSource)) {
                foreach ($overwriteBase as $mapBase) {
                    $sameValues = false;
                    foreach ($overwriteSource as $mapSource) {
                        if ($mapBase->getEcuSwParameterValueSet()->getEcuSwParameter()->getEcuSwParameterId()
                            == $mapSource->getEcuSwParameterValueSet()->getEcuSwParameter()->getEcuSwParameterId()) {
                            if (
                                $mapBase->getEcuSwParameterValueSet()->getValueUnsigned() == $mapSource->getEcuSwParameterValueSet()->getValueUnsigned()
                                && $mapBase->getEcuSwParameterValueSet()->getValueString() == $mapSource->getEcuSwParameterValueSet()->getValueString()
                                && $mapBase->getEcuSwParameterValueSet()->getValueInteger() == $mapSource->getEcuSwParameterValueSet()->getValueInteger()
                                && $mapBase->getEcuSwParameterValueSet()->getValueBool() == $mapSource->getEcuSwParameterValueSet()->getValueBool()
                                && $mapBase->getEcuSwParameterValueSet()->getValueDouble() == $mapSource->getEcuSwParameterValueSet()->getValueDouble()
                            ) {
                                $sameValues = true;
                                break;
                            }
                        }
                    }
                    if ($sameValues == false) {
                        return null;
                    }
                }
                return $subconf->getSourceSubVehicleConfiguration()->getSubVehicleConfigurationName();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param HeaderModel              $header
     * @param ConfigurationEcus        $ecu
     * @param SubVehicleConfigurations $subConf
     *
     * @throws Exception
     */
    public function save(HeaderModel $header, ConfigurationEcus $ecu, SubVehicleConfigurations $subConf): void
    {

        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            /* First save header basic data */
            $containmentManager = $this->entityManager->getRepository(EcuSubConfigurationVehicleContainment::class);

            $subconfMapping = $containmentManager->findOneBy(['ceEcu' => $ecu, 'subVehicleConfiguration' => $subConf]);

            $subconfMapping->setOdxSourceType($header->getOdxSourceType());

            $this->entityManager->persist($subconfMapping);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }
}