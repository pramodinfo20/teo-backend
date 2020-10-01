<?php

namespace App\Service\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSoftwareParameterNames;
use App\Entity\EcuSwParameterEcuSwVersionMapping;
use App\Entity\EcuSwParameters;
use App\Entity\EcuSwParameterSerialStates;
use App\Entity\EcuSwParameterTypes;
use App\Entity\EcuSwParameterValuesSets;
use App\Entity\EcuSwVersionGeneralProperties;
use App\Entity\EcuSwVersionLockStatus;
use App\Entity\EcuSwVersions;
use App\Entity\Odx1Parameters;
use App\Entity\ReleaseStatus;
use App\Entity\SecureAccessProperties;
use App\Entity\Units;
use App\Entity\VariableTypes;
use App\Enum\Entity\EcuCommunicationProtocols as EcuCommunicationProtocolsEnum;
use App\Enum\Entity\EcuSoftwareParameterNames as EcuSoftwareParameterNamesEnum;
use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use App\Enum\Entity\EcuSwVersionLockStatus as EcuSwVersionLockStatusEnum;
use App\Enum\Entity\ReleaseStatus as ReleaseStatusEnum;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;
use App\Enum\OdxVersions;
use App\Service\History\Ecu\Sw\HistorySoftwareVersionI;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class SoftwareVersion implements HistorySoftwareVersionI
{
    const DEFAULT_SECURE_ACCESS = 1; /* TODO: Is this really need? */
    const DEFAULT_RELEASE_STATUS = ReleaseStatusEnum::RELEASE_STATUS_IN_DEVELOPMENT;
    const DEFAULT_PROTOCOL = EcuCommunicationProtocolsEnum::ECU_COMMUNICATION_PROTOCOL_UDS;
    const DEFAULT_NOT_LOCKED = EcuSwVersionLockStatusEnum::ECU_VERSION_LOCK_STATUS_NOT_LOCKED;

    const DEFAULT_UNIT = 1; /* TODO: Map whole table to constants */
    /* Quick FIX - change Default variable type - cannot copy sts to HW and  SW */
    const DEFAULT_VARIABLE_TYPE = VariableTypesEnum::VARIABLE_TYPE_STRING;
    const DEFAULT_WINDCHILL = 'http://windchillapp.streetscooter.local/Windchill';

    /* Copy Software Version or Subversion
       COPY_SOFTWARE_VERSION                 = 1 ------ Copy Software version, $StsOrSuffix ------- Sts part number
       CREATE_SUBVERSION_AND_COPY_PARAMETERS = 2 ------ Create Subversion and copy parameters, $StsOrSuffix ------- suffix
    */
    const COPY_SOFTWARE_VERSION = 1;
    const CREATE_SUBVERSION_AND_COPY_PARAMETERS = 2;

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
     *
     * @throws \Exception
     */
    public function __construct(
        ObjectManager $manager,
        EntityManagerInterface $entityManager
    )
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    //todo: Add history module
    /**
     * Delete Software Version | Subversion by Id
     *
     * @param EcuSwVersions $sw
     *
     * @return void
     */
    public function deleteSwById(EcuSwVersions $sw): void
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);
        $entityManagerSwVersion = $this->entityManager->getRepository(EcuSwVersions::class);
        $entityManagerSwGP = $this->entityManager->getRepository(EcuSwVersionGeneralProperties::class);
        $entityManagerSerial = $this->entityManager->getRepository(EcuSwParameterSerialStates::class);

        $mapping = $entityManagerMapping->findBy(['ecuSwVersion' => $sw]);

        foreach ($mapping as $parameterMapping) {
            $parameter = $parameterMapping->getEcuSwParameter();

            list($default, $constant, $name, $id, $parameterType) = [
                $parameter->getUsedDefaultValue(),
                $parameter->getUsedConstantValue(),
                $parameter->getEcuSoftwareParameterName(),
                $parameter->getEcuSwParameterId(),
                $parameter->getEcuSwParameterType()->getEcuSwParameterTypeId()
            ];

            $this->entityManager->remove($parameterMapping);

            $odx1 = $entityManagerOdx1->find($id);

            if ($parameterType == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER) {
                $this->entityManager->remove($name);
            } elseif ($parameterType == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL) {
                $serialState = $entityManagerSerial->find($id);
                $this->entityManager->remove($serialState);
            }

            if ($odx1) {
                $this->entityManager->remove($odx1);
            }

            $this->entityManager->remove($parameter);
            $this->entityManager->flush();

            if ($default) {
                $this->entityManager->remove($default);
            } elseif ($constant) {
                $this->entityManager->remove($constant);
            }
        }

        $generalProperties = $entityManagerSwGP->findOneBy(['esvgpEcuSwVersion' => $sw]);
        $softwareVersion = $entityManagerSwVersion->find($sw);
        $this->entityManager->remove($generalProperties);
        $this->entityManager->remove($softwareVersion);

        $this->entityManager->flush();
    }

    /**
     * Create new Software Version
     *
     * @param ConfigurationEcus $ecu
     * @param string            $sw_version
     *
     * @return EcuSwVersions
     */
    public function createNewSw(ConfigurationEcus $ecu, string $sw_version): EcuSwVersions
    {
        $newSwVersion = new EcuSwVersions();
        $newSwVersion->setCeEcu($ecu);
        $newSwVersion->setEcuCommunicationProtocol($this->entityManager
            ->getRepository(EcuCommunicationProtocols::class)->findOneBy(
                ['ecuCommunicationProtocolId' => self::DEFAULT_PROTOCOL]
            ));
        $newSwVersion->setEcuSwVersionLockStatus($this->entityManager
            ->getRepository(EcuSwVersionLockStatus::class)->findOneBy(
                ['ecuSwVersionLockStatusId' => self::DEFAULT_NOT_LOCKED]
            ));
        $newSwVersion->setParentSwVersion(null);
        $newSwVersion->setSwVersion($sw_version);
        $newSwVersion->setSuffixIfIsSubEcuSwVersion(null);
        $newSwVersion->setReleaseStatus($this->entityManager
            ->getRepository(ReleaseStatus::class)->findOneBy(
                ['releaseStatusId' => self::DEFAULT_RELEASE_STATUS]
            ));
        $newSwVersion->setOdxVersion(OdxVersions::ODX_VERSION_2);

        $this->entityManager->persist($newSwVersion);
        $this->entityManager->flush();

        $newSwGP = new EcuSwVersionGeneralProperties();
        $newSwGP->setEsvgpEcuSwVersion($newSwVersion);
        $newSwGP->setIsBigEndian(false);
        $newSwGP->setInformation('');
        $newSwGP->setSecureAccessProperties($this->entityManager
            ->getRepository(SecureAccessProperties::class)->findOneBy(
                ['secureAccessPropertiesId' => self::DEFAULT_SECURE_ACCESS]
            ));
        $newSwGP->setUdsRequestId('\x0');
        $newSwGP->setUdsResponseId('\x0');
        $newSwGP->setWindchillLink(self::DEFAULT_WINDCHILL);

        $this->entityManager->persist($newSwGP);
        $this->entityManager->flush();

        $this->fillNewSw($ecu, $newSwVersion);

        return $newSwVersion;
    }

    /**
     * Fill new Software Version with HW,SW,SERIAL for Odx1 and Odx2
     *
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $softwareVersion
     *
     * @return void
     */
    public function fillNewSw(ConfigurationEcus $ecu, EcuSwVersions $softwareVersion): void
    {
        $entityManagerParameterType = $this->entityManager->getRepository(EcuSwParameterTypes::class);
        $entityManagerParameterName = $this->entityManager->getRepository(EcuSoftwareParameterNames::class);
        $entityManagerProtocol = $this->entityManager->getRepository(EcuCommunicationProtocols::class);
        $entityManagerUnit = $this->entityManager->getRepository(Units::class);
        $entityManagerVariableType = $this->entityManager->getRepository(VariableTypes::class);

        $softwareCommunicationProtocol =
            ($softwareVersion->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName() == 'UDS+XCP') ?
                $entityManagerProtocol->find(self::DEFAULT_PROTOCOL) : null;
//        $unit = $entityManagerUnit->find(self::DEFAULT_UNIT);
        $unit = null;
        $variableType = $entityManagerVariableType->find(self::DEFAULT_VARIABLE_TYPE);

        /*--------------- HW -------------------*/
        $hw = new EcuSwParameters();
        $hw->setDataIdentifier('\x1');
        $hw->setStartBit(1);
        $hw->setStopBit(1);
        $hw->setNumberOfBytes(1);
        $hw->setFactor(1);
        $hw->setParameterOffset(1);
        $hw->setShouldReadParameterValueFromEcu(true);
        $hw->setShouldWriteParameterValueToEcu(true);
        $hw->setShouldConfirmParameterValueFromEcu(true);
        $hw->setParameterOrder(0);
        $hw->setEcuSwParameterType($entityManagerParameterType->find(EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW));
        $hw->setUsedConstantValue(null);
        $hw->setDynamicParameterValuesByDiagnosticSoftware(null);
        $hw->setEcuSoftwareParameterName($entityManagerParameterName->find(
            EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_HW_STS
        ));
        $hw->setEcuCommunicationProtocol($softwareCommunicationProtocol);
        $hw->setUnit($unit);
        $hw->setLinkedToCocParameter(null);
        $hw->setLinkedToGlobalParameter(null);
        $hw->setVariableType($variableType);
        $hw->setCeEcu($ecu);
        $hw->setActivated(true);
        $hw->setIsBigEndian(false);

        $this->entityManager->persist($hw);

        $hwValueSet = new EcuSwParameterValuesSets();
        $hwValueSet->setValueInteger(0);
        $hwValueSet->setEcuSwParameter($hw);

        $hw->setUsedDefaultValue($hwValueSet);

        $this->entityManager->persist($hwValueSet);

        $hwOdx1 = new Odx1Parameters();
        $hwOdx1->setOpEcuSwParameter($hw);
        $hwOdx1->setIsAlsoOdx2(true);
        $hwOdx1->setSpecialOrderIdForOdx1(0);

        $this->entityManager->persist($hwOdx1);

        $hwMapping = new EcuSwParameterEcuSwVersionMapping();
        $hwMapping->setEcuSwVersion($softwareVersion);
        $hwMapping->setEcuSwParameter($hw);

        $this->entityManager->persist($hwMapping);
        /*--------------------------------------*/

        /*--------------- SW -------------------*/
        $sw = new EcuSwParameters();
        $sw->setDataIdentifier('\x1');
        $sw->setStartBit(1);
        $sw->setStopBit(1);
        $sw->setNumberOfBytes(1);
        $sw->setFactor(1);
        $sw->setParameterOffset(1);
        $sw->setShouldReadParameterValueFromEcu(true);
        $sw->setShouldWriteParameterValueToEcu(true);
        $sw->setShouldConfirmParameterValueFromEcu(true);
        $sw->setParameterOrder(0);
        $sw->setEcuSwParameterType($entityManagerParameterType->find(EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW));
        $sw->setUsedDefaultValue(null);
        $sw->setDynamicParameterValuesByDiagnosticSoftware(null);
        $sw->setEcuSoftwareParameterName($entityManagerParameterName->find(
            EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_SW_STS
        ));
        $sw->setEcuCommunicationProtocol($softwareCommunicationProtocol);
        $sw->setUnit($unit);
        $sw->setLinkedToCocParameter(null);
        $sw->setLinkedToGlobalParameter(null);
        $sw->setVariableType($variableType);
        $sw->setCeEcu($ecu);
        $sw->setActivated(true);
        $sw->setIsBigEndian(false);

        $this->entityManager->persist($sw);

        $swValueSet = new EcuSwParameterValuesSets();
        $swValueSet->setValueInteger(0);
        $swValueSet->setEcuSwParameter($sw);

        $sw->setUsedConstantValue($swValueSet);

        $this->entityManager->persist($swValueSet);

        $swOdx1 = new Odx1Parameters();
        $swOdx1->setOpEcuSwParameter($sw);
        $swOdx1->setIsAlsoOdx2(true);
        $swOdx1->setSpecialOrderIdForOdx1(0);

        $this->entityManager->persist($swOdx1);

        $swMapping = new EcuSwParameterEcuSwVersionMapping();
        $swMapping->setEcuSwVersion($softwareVersion);
        $swMapping->setEcuSwParameter($sw);

        $this->entityManager->persist($swMapping);
        /*--------------------------------------*/

        /*--------------- SERIAL -------------------*/
        $serial = new EcuSwParameters();
        $serial->setDataIdentifier('\x1');
        $serial->setStartBit(1);
        $serial->setStopBit(1);
        $serial->setNumberOfBytes(1);
        $serial->setFactor(1);
        $serial->setParameterOffset(1);
        $serial->setShouldReadParameterValueFromEcu(true);
        $serial->setShouldWriteParameterValueToEcu(false);
        $serial->setShouldConfirmParameterValueFromEcu(false);
        $serial->setParameterOrder(0);
        $serial->setEcuSwParameterType($entityManagerParameterType->find(EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL));
        $serial->setUsedConstantValue(null);
        $serial->setDynamicParameterValuesByDiagnosticSoftware(null);
        $serial->setEcuSoftwareParameterName($entityManagerParameterName->find(
            EcuSoftwareParameterNamesEnum::ECU_PARAMETER_NAME_SERIAL
        ));
        $serial->setEcuCommunicationProtocol($softwareCommunicationProtocol);
        $serial->setUnit($unit);
        $serial->setLinkedToCocParameter(null);
        $serial->setLinkedToGlobalParameter(null);
        $serial->setVariableType($variableType);
        $serial->setCeEcu($ecu);
        $serial->setActivated(true);
        $serial->setIsBigEndian(false);

        $this->entityManager->persist($serial);

        $serialValueSet = new EcuSwParameterValuesSets();
        $serialValueSet->setValueInteger(0);
        $serialValueSet->setEcuSwParameter($serial);

        $serial->setUsedDefaultValue($serialValueSet);

        $this->entityManager->persist($serialValueSet);

        $serialState = new EcuSwParameterSerialStates();
        $serialState->setEcuSwParameter($serial);
        $serialState->setSerialState(true);

        $this->entityManager->persist($serialState);

        $serialMapping = new EcuSwParameterEcuSwVersionMapping();
        $serialMapping->setEcuSwVersion($softwareVersion);
        $serialMapping->setEcuSwParameter($serial);

        $this->entityManager->persist($serialMapping);
        /*------------------------------------------*/

        $this->entityManager->flush();
    }

    /**
     * Create new SubVersion
     *
     * @param EcuSwVersions $sw
     * @param string        $suffix
     * @param string        $flag
     *
     * @return EcuSwVersions|null
     */
    public function createNewSubversion(EcuSwVersions $sw, string $suffix, string $flag): ?EcuSwVersions
    {
        if ($flag == 'false') {
            $ecu = $this->entityManager
                ->getRepository(ConfigurationEcus::class)->findOneBy(['ceEcuId' => $sw->getCeEcu()]);

            $newSubVersion = new EcuSwVersions();
            $newSubVersion->setCeEcu($ecu);
            $newSubVersion->setEcuCommunicationProtocol($this->entityManager
                ->getRepository(EcuCommunicationProtocols::class)->findOneBy(
                    ['ecuCommunicationProtocolId' => self::DEFAULT_PROTOCOL]
                ));
            $newSubVersion->setEcuSwVersionLockStatus($this->entityManager
                ->getRepository(EcuSwVersionLockStatus::class)->findOneBy(
                    ['ecuSwVersionLockStatusId' => self::DEFAULT_NOT_LOCKED]
                ));
            $newSubVersion->setParentSwVersion($sw);
            $newSubVersion->setSwVersion($sw->getSwVersion());
            $newSubVersion->setSuffixIfIsSubEcuSwVersion($suffix);
            $newSubVersion->setReleaseStatus($this->entityManager
                ->getRepository(ReleaseStatus::class)->findOneBy(
                    ['releaseStatusId' => self::DEFAULT_RELEASE_STATUS]
                ));
            $newSubVersion->setStsPartNumber($sw->getStsPartNumber());
            $newSubVersion->setOdxVersion(OdxVersions::ODX_VERSION_2);

            $this->entityManager->persist($newSubVersion);
            $this->entityManager->flush();

            $newSwGP = new EcuSwVersionGeneralProperties();
            $newSwGP->setEsvgpEcuSwVersion($newSubVersion);
            $newSwGP->setIsBigEndian(false);
            $newSwGP->setInformation('');
            $newSwGP->setSecureAccessProperties($this->entityManager
                ->getRepository(SecureAccessProperties::class)->findOneBy(
                    ['secureAccessPropertiesId' => self::DEFAULT_SECURE_ACCESS]
                ));
            $newSwGP->setUdsRequestId('\x0');
            $newSwGP->setUdsResponseId('\x0');
            $newSwGP->setWindchillLink(self::DEFAULT_WINDCHILL);

            $this->entityManager->persist($newSwGP);
            $this->entityManager->flush();

            $this->fillNewSw($ecu, $newSubVersion);

            return $newSubVersion;
        } else {
            return $this->copySwById($sw, $suffix, 2);
        }
    }

    /**
     * Copy Software Version | Subversion by Id
     *
     * @param EcuSwVersions $sw
     * @param string        $StsOrSuffix
     * @param int           $flag
     *
     * @return EcuSwVersions
     */
    public function copySwById(EcuSwVersions $sw, string $StsOrSuffix, int $flag = self::COPY_SOFTWARE_VERSION): EcuSwVersions
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);
        $entityManagerSwGP = $this->entityManager->getRepository(EcuSwVersionGeneralProperties::class);
        $entityManagerSerial = $this->entityManager->getRepository(EcuSwParameterSerialStates::class);

        $newSwVersion = clone $sw;
        $newSwGP = clone $entityManagerSwGP->find($sw);

        if ($flag == self::COPY_SOFTWARE_VERSION) {
            $newSwVersion->setSwVersion($StsOrSuffix);
        } elseif ($flag == self::CREATE_SUBVERSION_AND_COPY_PARAMETERS) {
            $newSwVersion->setSuffixIfIsSubEcuSwVersion($StsOrSuffix);
            $newSwVersion->setParentSwVersion($sw);
        }

        $newSwVersion->setEcuSwVersionLockStatus($this->entityManager
            ->getRepository(EcuSwVersionLockStatus::class)->findOneBy(
                ['ecuSwVersionLockStatusId' => self::DEFAULT_NOT_LOCKED]
            ));
        $newSwVersion->setReleaseStatus($this->entityManager
            ->getRepository(ReleaseStatus::class)->findOneBy(
                ['releaseStatusId' => self::DEFAULT_RELEASE_STATUS]
            ));

        $newSwGP->setEsvgpEcuSwVersion($newSwVersion);
        $this->entityManager->persist($newSwVersion);
        $this->entityManager->flush();
        $this->entityManager->persist($newSwGP);
        $this->entityManager->flush();

        $oldParametersMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $sw]);

        foreach ($oldParametersMapping as $oldParameterMap) {
            $oldParameter = $oldParameterMap->getEcuSwParameter();
            $newParameter = clone $oldParameter;

            list($default, $constant, $name, $id, $parameterType) = [
                $oldParameter->getUsedDefaultValue(),
                $oldParameter->getUsedConstantValue(),
                $oldParameter->getEcuSoftwareParameterName(),
                $oldParameter->getEcuSwParameterId(),
                $oldParameter->getEcuSwParameterType()->getEcuSwParameterTypeId()
            ];

            $oldOdx1 = $entityManagerOdx1->find($id);

            if ($parameterType == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER) {
                $newParameterName = new EcuSoftwareParameterNames();
                $newParameterName->setEcuSoftwareParameterName($name->getEcuSoftwareParameterName());
                $this->entityManager->persist($newParameterName);
                $this->entityManager->flush();
                $newParameter->setEcuSoftwareParameterName($newParameterName);
            } elseif ($parameterType == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL) {
                $oldSerialState = $entityManagerSerial->find($id);
                $newSerialState = new EcuSwParameterSerialStates();
                $newSerialState->setEcuSwParameter($newParameter);
                $newSerialState->setSerialState($oldSerialState->getSerialState());
                $this->entityManager->persist($newSerialState);
            }

            if ($oldOdx1) {
                $newOdx1 = new Odx1Parameters();
                $newOdx1->setOpEcuSwParameter($newParameter);
                $newOdx1->setIsAlsoOdx2($oldOdx1->getIsAlsoOdx2());
                $newOdx1->setSpecialOrderIdForOdx1($oldOdx1->getSpecialOrderIdForOdx1());
                $this->entityManager->persist($newOdx1);
            }

            if ($default) {
                $newValueSet = clone $default;
                $newParameter->setUsedDefaultValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            } elseif ($constant) {
                $newValueSet = clone $constant;
                $newParameter->setUsedConstantValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            }

            $this->entityManager->persist($newParameter);

            $parameterSwMapping = new EcuSwParameterEcuSwVersionMapping();
            $parameterSwMapping->setEcuSwParameter($newParameter);
            $parameterSwMapping->setEcuSwVersion($newSwVersion);
            $this->entityManager->persist($parameterSwMapping);
        }

        $this->entityManager->flush();

        return $newSwVersion;
    }

    /**
     * Get other sw by ecu id
     *
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     *
     * @return array
     */
    public function getOtherSwByEcu(ConfigurationEcus $ecu, EcuSwVersions $sw): array
    {
        $sws = $this->entityManager->getRepository(EcuSwVersions::class)
            ->findOtherSwByEcuId($ecu, $sw, $sw->getEcuCommunicationProtocol());

        return $sws;
    }
}