<?php

namespace App\Service\Ecu\Sw;

use App\Converter\ASCIICdisStrategy;
use App\Converter\ASCIIStrategy;
use App\Converter\BlobStrategy;
use App\Converter\BoolStrategy;
use App\Converter\IntegerStrategy;
use App\Converter\StringStrategy;
use App\Converter\UnsignedStrategy;
use App\Entity\CocParameters;
use App\Entity\ConfigurationEcus;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSoftwareParameterNames;
use App\Entity\EcuSwParameterEcuSwVersionMapping;
use App\Entity\EcuSwParameters;
use App\Entity\EcuSwParameterTypes;
use App\Entity\EcuSwParameterValuesSets;
use App\Entity\EcuSwVersionGeneralProperties;
use App\Entity\EcuSwVersions;
use App\Entity\GlobalParameters;
use App\Entity\Odx1Parameters;
use App\Entity\Units;
use App\Entity\VariableTypes;
use App\Enum\Entity\EcuCommunicationProtocols as EcuCommunicationProtocolsEnum;
use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;

use App\Model\Odx1Collection;
use App\Model\Odx1Parameter;
use App\Model\Odx2Collection;
use App\Model\Odx2Parameter;
use App\Model\OdxCollection;
use App\Service\History\Ecu\Sw\HistoryParameterI;
use App\Utils\Dictionary;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Enum\Entity\ConfigurationEcus as EcusConstants;

class Parameter implements HistoryParameterI
{
    const LINKING_TYPE_DEFAULT = 1;
    const LINKING_TYPE_CONSTANT = 2;
    const LINKING_TYPE_GLOBAL_PARAMETER = 3;
    const LINKING_TYPE_DYNAMIC_VALUE = 4;
    /*const LINKING_TYPE_COC_PARAMETER = 5;*/

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var VariableTypes[]
     */
    private $variableTypes;

    /**
     * @var Units[]
     */
    private $units;

    /**
     * @var EcuSwParameterTypes[]
     */
    private $parameterTypes;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     *
     * @throws Exception
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * Map int to string linking type
     *
     * @param int $linkingType
     *
     * @return string
     */
    public static function getLinkingTypeById(int $linkingType): string
    {
        switch ($linkingType) {
            case Parameter::LINKING_TYPE_DEFAULT:
            default:
                return 'Default';
            case Parameter::LINKING_TYPE_CONSTANT:
                return 'Constant';
            case Parameter::LINKING_TYPE_GLOBAL_PARAMETER:
                return 'Global Parameter';
            case Parameter::LINKING_TYPE_DYNAMIC_VALUE:
                return 'Dynamic';
            /*case Parameter::LINKING_TYPE_COC_PARAMETER:
                return 'CoC';*/
        }
    }

    /**
     * @param int $odx
     * @param string $headerProtocol
     * @param EcuSwVersions|null $sw
     *
     * @return Odx2Collection
     */
    public function getParameters(int $odx, string $headerProtocol, EcuSwVersions $sw = null): OdxCollection
    {
        if (!$sw)
            return ($odx == 1) ? new Odx1Collection() : new Odx2Collection();

        if ($odx == 1) {
            return $this->getOdx1Parameters($sw);
        } else {
            return $this->getOdx2Parameters($sw, $headerProtocol);
        }
    }

    /**
     * Retrieve parameters for ODX1 and set it in model
     *
     * @param EcuSwVersions $sw
     *
     * @return Odx1Collection
     */
    private function getOdx1Parameters(EcuSwVersions $sw): Odx1Collection
    {
        $parametersCollection = new Odx1Collection();
        $odxSts02 = $sw->getCeEcu()->getDiagnosticSoftwareSupportsStsOdx2ForThisEcu();

        $parameters = $this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMapping::class)
            ->getParametersOdx1BySwId($sw);

        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $parameter) {
                $odx1Parameter = new Odx1Parameter();
                $odx1Parameter->setOdxSts02($odxSts02);
                $odx1Parameter->setOrder($parameter['order']);
                $odx1Parameter->setName($parameter['name']);
                $odx1Parameter->setNameId($parameter['name_id']);
                $odx1Parameter->setOdx2(filter_var($parameter['odx2'], FILTER_VALIDATE_BOOLEAN));
                $odx1Parameter->setRead((bool)$parameter['read']);
                $odx1Parameter->setWrite((bool)$parameter['write']);
                $odx1Parameter->setConfirm((bool)$parameter['confirm']);
                $odx1Parameter->setType($parameter['type_order']);
                $odx1Parameter->setVariableType($parameter['type']);
                $odx1Parameter->setUnit($parameter['unit']);
                $odx1Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                        $parameter['value_string'] : null));
                $odx1Parameter->setValueBool($parameter['value_bool']);
                $odx1Parameter->setValueInteger($parameter['value_integer']);
                $odx1Parameter->setValueUnsigned($parameter['value_unsigned']);
                $odx1Parameter->setLinkingType($parameter['linking_type']);
                $odx1Parameter->setParameterId($parameter['parameter_id']);
                $odx1Parameter->setLinkedValueName((!in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                    $parameter['value_string'] : null));

                if (isset($parameter['global_parameter_id'])) {
                    $globalParameter = $this->manager
                        ->getRepository(GlobalParameters::class)
                        ->find($parameter['global_parameter_id']);

                    $odx1Parameter->setLinkedToGlobalParameter($globalParameter);
                }

                if (isset($parameter['dynamic_value_id'])) {
                    $dynamicValue = $this->manager
                        ->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($parameter['dynamic_value_id']);

                    $odx1Parameter->setDynamicParameterValuesByDiagnosticSoftware($dynamicValue);
                }

                /*if (isset($parameter['coc_parameter_id'])) {
                    $cocParameter = $this->manager
                        ->getRepository(CocParameters::class)
                        ->find($parameter['coc_parameter_id']);

                    $odx1Parameter->setLinkedToCoCParameter($cocParameter);
                }*/

                $parametersCollection->addParameters($odx1Parameter);
            }
        }

        return $parametersCollection;
    }

    /**
     * Map string to int linking type
     *
     * @param string $linkingType
     *
     * @return int
     */
    public static function getLinkingTypeByName(string $linkingType): int
    {
        switch ($linkingType) {
            case 'Default':
            default:
                return Parameter::LINKING_TYPE_DEFAULT;
            case 'Constant':
                return Parameter::LINKING_TYPE_CONSTANT;
            case 'Global Parameter':
                return Parameter::LINKING_TYPE_GLOBAL_PARAMETER;
            case 'Dynamic':
                return Parameter::LINKING_TYPE_DYNAMIC_VALUE;
     /*       case 'CoC':
                return Parameter::LINKING_TYPE_COC_PARAMETER;*/
        }
    }

    /**
     * Retrieve parameters for ODX2 and set it in model
     *
     * @param EcuSwVersions $sw
     * @param               $headerProtocol
     *
     * @return Odx2Collection
     */
    private function getOdx2Parameters(EcuSwVersions $sw, $headerProtocol): Odx2Collection
    {
        $parametersCollection = new Odx2Collection();

        $parameters = $this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMapping::class)
            ->getParametersOdx2BySwId($sw);

        $odxSts02 = $sw->getCeEcu()->getDiagnosticSoftwareSupportsStsOdx2ForThisEcu();

        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $parameter) {
                $odx2Parameter = new Odx2Parameter();
                $odx2Parameter->setCoding($parameter['coding']);
                $odx2Parameter->setBigEndian($parameter['isBigEndian']);
                $odx2Parameter->setOdxSts02($odxSts02);
                $odx2Parameter->setParameterId($parameter['parameter_id']);
                $odx2Parameter->setActivated($parameter['active']);
                $odx2Parameter->setOrder($parameter['order']);
                $odx2Parameter->setName($parameter['name']);
                $odx2Parameter->setNameId($parameter['name_id']);
                $odx2Parameter->setOdx1(filter_var($parameter['odx1'], FILTER_VALIDATE_BOOLEAN));
                $odx2Parameter->setHeaderProtocol($headerProtocol);

                $protocol = $this->manager->getRepository(EcuCommunicationProtocols::class)
                    ->findOneBy(['ecuCommunicationProtocolName' => $parameter['protocol']]);
                $odx2Parameter->setProtocol($protocol);
                $odx2Parameter->setRead((bool)$parameter['read']);
                $odx2Parameter->setWrite((bool)$parameter['write']);
                $odx2Parameter->setConfirm((bool)$parameter['confirm']);
                $odx2Parameter->setVariableType($parameter['type']);
                $odx2Parameter->setType($parameter['type_order']);
                $odx2Parameter->setBytes($parameter['bytes']);
                $odx2Parameter->setFactor($parameter['factor']);
                $odx2Parameter->setOffset($parameter['offset']);
                $odx2Parameter->setUnit($parameter['unit']);
                $odx2Parameter->setValueUnsigned($parameter['value_unsigned']);
                $odx2Parameter->setValueInteger($parameter['value_integer']);
                $odx2Parameter->setValueBool($parameter['value_bool']);
                $odx2Parameter->setValueHex($parameter['value_hex']);
                if (VariableTypesEnum::getVariableTypeByName($parameter['type']) ==
                    VariableTypesEnum::VARIABLE_TYPE_BLOB) {
                    $odx2Parameter->setValueBlob((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                        $parameter['value_string'] : null));
                } else {
                    $odx2Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                        $parameter['value_string'] : null));
                }
                $odx2Parameter->setLinkingType(Parameter::getLinkingTypeByName($parameter['linking_type']));
                $odx2Parameter->setStartBit($parameter['start_bit']);
                $odx2Parameter->setStopBit($parameter['stop_bit']);
                $odx2Parameter->setUdsId((!is_null($parameter['uds_id'])) ? (($parameter['uds_id'] != 'DUMMY_VALUE') ?
                    substr($parameter['uds_id'], 2) : null) : $parameter['uds_id']);
                $odx2Parameter->setSerialState($parameter['serial_state']);
                $odx2Parameter->setLinkingTypeName($parameter['linking_type']);
                $odx2Parameter->setLinkedValueName((!in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                    $parameter['value_string'] : null));

                if (isset($parameter['global_parameter_id'])) {
                    $globalParameter = $this->manager
                        ->getRepository(GlobalParameters::class)
                        ->find($parameter['global_parameter_id']);

                    $odx2Parameter->setLinkedToGlobalParameter($globalParameter);
                }

                if (isset($parameter['dynamic_value_id'])) {
                    $dynamicValue = $this->manager
                        ->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($parameter['dynamic_value_id']);

                    $odx2Parameter->setDynamicParameterValuesByDiagnosticSoftware($dynamicValue);
                }

          /*      if (isset($parameter['coc_parameter_id'])) {
                    $cocParameter = $this->manager
                        ->getRepository(CocParameters::class)
                        ->find($parameter['coc_parameter_id']);

                    $odx2Parameter->setLinkedToCoCParameter($cocParameter);
                }*/

                $parametersCollection->addParameters($odx2Parameter);
            }
        }

        return $parametersCollection;
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param OdxCollection $collection
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions $sw
     * @param array $parametersBag
     * @param int $odx
     *
     * @throws Exception
     */
    public function save(
        OdxCollection $collection, ConfigurationEcus $ecu, EcuSwVersions $sw, array $parametersBag, int $odx
    ): void
    {
        /* Keep dictionary data to save resources */
        $this->variableTypes = Dictionary::transformToDictionary(
            $this->manager->getRepository(VariableTypes::class)->findAll(),
            'variableTypeName'
        );

        $this->units = Dictionary::transformToDictionary(
            $this->manager->getRepository(Units::class)->findAll(),
            'unitName'
        );

        $this->parameterTypes = Dictionary::transformToDictionary(
            $this->manager->getRepository(EcuSwParameterTypes::class)->findAll(),
            'parameterType',
            'ecuSwParameterTypeId'
        );

        if ($odx == 2) {
            $this->saveOdx2Parameters($collection, $ecu, $sw, $parametersBag);
        } else {
            $this->saveOdx1Parameters($collection);
        }
    }

    /**
     * Save Odx2 Parameters
     *
     * @param OdxCollection $collection
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions $sw
     * @param array $parametersBag
     *
     * @throws Exception
     */
    private function saveOdx2Parameters(
        OdxCollection $collection,
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        array $parametersBag
    ): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $proceededParameters = [];
        $odx2Order = 0;

        /* Quick Fix - lack of protocol && incorrect protocol for new parameters */
        /* HW exists always with correct header protocol */
        foreach ($collection->getParameters() as $parameter) {
            if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW) {
                $headerProtocol = EcuCommunicationProtocolsEnum::getProtocolIdByName($parameter->getHeaderProtocol());
                break;
            }
        }

        try {
            foreach ($collection->getParameters() as $parameter) {
                /* Decide to add or to edit parameters */
                if ($parameter->getParameterId()) {
                    $ecuParameter = $this->entityManager->getRepository(EcuSwParameters::class)
                        ->find($parameter->getParameterId());
                } else {
                    $ecuParameter = new EcuSwParameters();
                }

                /* Activated */
                $ecuParameter->setActivated($parameter->isActivated());

                /* Ecu */
                $ecuParameter->setCeEcu($ecu);

                /* Name */
                switch ($parameter->getType()) {
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW:
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW:

                        $parameterName = $this->entityManager->getRepository(EcuSoftwareParameterNames::class)
                            ->findOneBy(['ecuSoftwareParameterNameId' => $parameter->getNameId()]);
                        $ecuParameter->setEcuSoftwareParameterName($parameterName);
                        break;
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL:
                        /* Do not nothing, SERIAL must be constant */
                        break;
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER:

                        $parameterName = $this->entityManager->getRepository(EcuSoftwareParameterNames::class)
                            ->findOneBy(['ecuSoftwareParameterNameId' => $parameter->getNameId()]);

                        if ($parameterName) {
                            $ecuParameter->setEcuSoftwareParameterName($parameterName->setEcuSoftwareParameterName($parameter->getName()));

                        } else {
                            $parameterToDb = new EcuSoftwareParameterNames();
                            $parameterToDb->setEcuSoftwareParameterName($parameter->getName());

                            $this->entityManager->persist($parameterToDb);

                            $ecuParameter->setEcuSoftwareParameterName($parameterToDb);
                        }
                      $ecuParameter->setParameterOrder(++$odx2Order);
                      break;
                }

                /* Type */
                if (isset($this->parameterTypes[$parameter->getType()])) {
                    $ecuParameter->setEcuSwParameterType($this->parameterTypes[$parameter->getType()]);
                }

                /* Protocol */
                if ($parameter->getProtocol()) {
                    $protocol = $this->entityManager->getRepository(EcuCommunicationProtocols::class)
                        ->find($parameter->getProtocol());

                    $ecuParameter->setEcuCommunicationProtocol($protocol);
                }

                /* Quick Fix */
                // $headerProtocol = EcuCommunicationProtocols::getProtocolIdByName($parameter->getHeaderProtocol());

                if ($headerProtocol != EcuCommunicationProtocolsEnum::ECU_COMMUNICATION_PROTOCOL_UDS_XCP) {
                    $ecuParameter->setEcuCommunicationProtocol(null);
                }

                if ($headerProtocol == EcuCommunicationProtocolsEnum::ECU_COMMUNICATION_PROTOCOL_XCP) {
                    $ecuParameter->setDataIdentifier(null);
                }

                /* UDS */
                if ($parameter->getUdsId()) {
                    $udsIdSubstr = substr(strtolower($parameter->getUdsId()), 0, 2);
                    if ($udsIdSubstr == "0x") {
                        $ecuParameter->setDataIdentifier($parameter->getUdsId());
                    } else {
                        $ecuParameter->setDataIdentifier("0x" . $parameter->getUdsId());
                    }
                }

                /* R/W/C */
                $ecuParameter->setShouldReadParameterValueFromEcu($parameter->isRead());
                $ecuParameter->setShouldWriteParameterValueToEcu($parameter->isWrite());
                $ecuParameter->setShouldConfirmParameterValueFromEcu($parameter->isConfirm());

                /* Unit */
                if (isset($this->units[$parameter->getUnit()])) {
                    $ecuParameter->setUnit($this->units[$parameter->getUnit()]);
                }

                /* Variable Type */
                if (isset($this->variableTypes[$parameter->getVariableType()])) {
                    $ecuParameter->setVariableType($this->variableTypes[$parameter->getVariableType()]);
                }

                /* New - coding */
                $ecuParameter->setCoding($parameter->getCoding());

                /* New - bigEndian */
                $ecuParameter->setIsBigEndian($parameter->isBigEndian());

                /* Bytes, Factor, Offset, Start & Stop Bit */
                $ecuParameter->setNumberOfBytes($parameter->getBytes());
                $ecuParameter->setFactor($parameter->getFactor());
                $ecuParameter->setParameterOffset($parameter->getOffset());
                $ecuParameter->setStartBit($parameter->getStartBit());
                $ecuParameter->setStopBit($parameter->getStopBit());

                $this->entityManager->persist($ecuParameter);
                $this->entityManager->flush();

                /* Values */
                if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER) {
                    if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT
                        || $parameter->getLinkingType() == self::LINKING_TYPE_CONSTANT
                    ) {
                        /* Clear previously values */
                        $this->clearValues($ecuParameter);

                        /* Create new one */
                        $newValue = new EcuSwParameterValuesSets();
                        $newValue->setEcuSwParameter($ecuParameter);

                        switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                            case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                                $newValue->setValueString($parameter->getValueBlob());
                                $newValue->setValueHex(
                                    (new BlobStrategy())->convertToHex((string)$parameter->getValueBlob(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                                $newValue->setValueUnsigned($parameter->getValueUnsigned());
                                $newValue->setValueHex(
                                    (new UnsignedStrategy())->convertToHex((string)$parameter->getValueUnsigned(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                            case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                            case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                                $newValue->setValueInteger($parameter->getValueInteger());
                                $newValue->setValueHex(
                                    (new IntegerStrategy())->convertToHex((string)$parameter->getValueInteger(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                                $newValue->setValueBool($parameter->isValueBool());
                                $newValue->setValueHex(
                                    (new BoolStrategy())->convertToHex((string)$parameter->isValueBool(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                                $newValue->setValueString($parameter->getValueString());
                                if ($ecu->getEcuName() == EcusConstants::CDIS_NAME) {
                                    $newValue->setValueHex(
                                        (new ASCIICdisStrategy())->convertToHex((string)$parameter->getValueString(),
                                            $parameter->getBytes(), $parameter->isBigEndian()
                                        ));
                                } else {
                                    $newValue->setValueHex(
                                        (new ASCIIStrategy())->convertToHex((string)$parameter->getValueString(),
                                            $parameter->getBytes(), $parameter->isBigEndian()
                                        ));
                                }
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_STRING:
                                $newValue->setValueString($parameter->getValueString());
                                $newValue->setValueHex(
                                    (new StringStrategy())->convertToHex((string)$parameter->getValueString(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                                break;
                        }

                        if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT) {
                            $ecuParameter->setUsedDefaultValue($newValue);
                        } else {
                            $ecuParameter->setUsedConstantValue($newValue);
                        }

                        $this->entityManager->persist($newValue);
                    }
                } elseif (
                    $parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW
                    || $parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW
                ) {
                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    /* Create new one */
                    $newValue = new EcuSwParameterValuesSets();
                    $newValue->setEcuSwParameter($ecuParameter);

                    switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                        case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                            $newValue->setValueString($parameter->getValueBlob());
                            $newValue->setValueHex(
                                (new BlobStrategy())->convertToHex((string)$parameter->getValueBlob(),
                                    $parameter->getBytes(), $parameter->isBigEndian()
                                ));
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                            $newValue->setValueUnsigned($parameter->getValueUnsigned());
                            $newValue->setValueHex(
                                (new UnsignedStrategy())->convertToHex((string)$parameter->getValueUnsigned(),
                                    $parameter->getBytes(), $parameter->isBigEndian()
                                ));
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                        case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                        case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                            $newValue->setValueInteger($parameter->getValueInteger());
                            $newValue->setValueHex(
                                (new IntegerStrategy())->convertToHex((string)$parameter->getValueInteger(),
                                    $parameter->getBytes(), $parameter->isBigEndian()
                                ));
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                            $newValue->setValueBool($parameter->isValueBool());
                            $newValue->setValueHex(
                                (new BoolStrategy())->convertToHex((string)$parameter->isValueBool(),
                                    $parameter->getBytes(), $parameter->isBigEndian()
                                ));
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                            $newValue->setValueString($parameter->getValueString());
                            if ($ecu->getEcuName() == EcusConstants::CDIS_NAME) {
                                $newValue->setValueHex(
                                    (new ASCIICdisStrategy())->convertToHex((string)$parameter->getValueString(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                            } else {
                                $newValue->setValueHex(
                                    (new ASCIIStrategy())->convertToHex((string)$parameter->getValueString(),
                                        $parameter->getBytes(), $parameter->isBigEndian()
                                    ));
                            }
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_STRING:
                            $newValue->setValueString($parameter->getValueString());
                            $newValue->setValueHex(
                                (new StringStrategy())->convertToHex((string)$parameter->getValueString(),
                                    $parameter->getBytes(), $parameter->isBigEndian()
                                ));
                            break;
                    }

                    if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW) {
                        $ecuParameter->setUsedConstantValue($newValue);
                    } else {
                        if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT) {
                            $ecuParameter->setUsedDefaultValue($newValue);
                        } else {
                            $ecuParameter->setUsedConstantValue($newValue);
                        }
                    }

                    $this->entityManager->persist($newValue);
                }

                /* Link to Global Parameter */
                if ($parameter->getLinkingType() == self::LINKING_TYPE_GLOBAL_PARAMETER) {
                    $globalParameter = $this->entityManager->getRepository(GlobalParameters::class)
                        ->find($parameter->getLinkedToGlobalParameter());

                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setLinkedToGlobalParameter($globalParameter);
                }

                /* Link to Dynamic */
                if ($parameter->getLinkingType() == self::LINKING_TYPE_DYNAMIC_VALUE) {
                    $dynamicParameter = $this->entityManager
                        ->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($parameter->getDynamicParameterValuesByDiagnosticSoftware());

                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setDynamicParameterValuesByDiagnosticSoftware($dynamicParameter);
                }

                /* Link to CoC */
          /*      if ($parameter->getLinkingType() == self::LINKING_TYPE_COC_PARAMETER) {
                    $cocParameter = $this->entityManager
                        ->getRepository(CocParameters::class)
                        ->find($parameter->getLinkedToCoCParameter());

                    // Clear previously values
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setLinkedToCocParameter($cocParameter);
                }*/

                /* Let's have some fun, connect every think to work */
                $ecuSwParameterToEcuSwVersion = $this->entityManager
                    ->getRepository(EcuSwParameterEcuSwVersionMapping::class)
                    ->findOneBy(['ecuSwVersion' => $sw, 'ecuSwParameter' => $ecuParameter]);

                if (!$ecuSwParameterToEcuSwVersion) {
                    $ecuSwParameterToEcuSwVersion = new EcuSwParameterEcuSwVersionMapping();
                    $ecuSwParameterToEcuSwVersion->setEcuSwVersion($sw);
                    $ecuSwParameterToEcuSwVersion->setEcuSwParameter($ecuParameter);

                    $this->entityManager->persist($ecuSwParameterToEcuSwVersion);
                }

                $this->entityManager->persist($ecuParameter);
                $this->entityManager->flush();

                $proceededParameters[$parameter->getParameterId()] = $parameter;
            }


            /* Is our parameter still needed? If not present in parameters bag, remove! */
            if ($missingParameters = $this->findMissingParameters($parametersBag, $proceededParameters)) {
                foreach ($missingParameters as $missingParameter) {
                    $this->deleteParameter($sw, $missingParameter);
                }
            }

            /* Reorder whole parameters collection */
            $this->reOrderParametersBySw($sw);

            /* Commit */
            $this->entityManager->commit();
        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
        $this->reOrderParametersBySw($sw);
    }

    /**
     * @param EcuSwParameters|null $ecuParameter
     */
    private function clearValues(?EcuSwParameters $ecuParameter = null)
    {
        if ($ecuParameter->getUsedDefaultValue()) {
            $this->entityManager->remove($ecuParameter->getUsedDefaultValue());

            $ecuParameter->setUsedDefaultValue(null);
        }

        if ($ecuParameter->getUsedConstantValue()) {
            $this->entityManager->remove($ecuParameter->getUsedConstantValue());

            $ecuParameter->setUsedConstantValue(null);
        }

        /*if ($ecuParameter->getLinkedToCocParameter()) {
            $ecuParameter->setLinkedToCocParameter(null);
        }*/

        if ($ecuParameter->getLinkedToGlobalParameter()) {
            $ecuParameter->setLinkedToGlobalParameter(null);
        }

        if ($ecuParameter->getDynamicParameterValuesByDiagnosticSoftware()) {
            $ecuParameter->setDynamicParameterValuesByDiagnosticSoftware(null);
        }
    }

    private function findMissingParameters(array $parametersBag, array $proceededParameters): array
    {
        $missing = [];

        foreach ($parametersBag as $id => $parameter) {
            if (!isset($proceededParameters[$id])) {
                $missing[] = $parameter;
            }
        }

        return $missing;
    }


    /**
     * Delete parameters
     *
     * @param EcuSwVersions $sw
     * @param Odx2Parameter $removedParameter
     */
    public function deleteParameter(EcuSwVersions $sw, Odx2Parameter $removedParameter): void
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);

        $mapping = $entityManagerMapping->findOneBy([
            'ecuSwVersion' => $sw,
            'ecuSwParameter' => $removedParameter->getParameterId()
        ]);
        $parameter = $mapping->getEcuSwParameter();

        list($default, $constant, $name) = [
            $parameter->getUsedDefaultValue(),
            $parameter->getUsedConstantValue(),
            $parameter->getEcuSoftwareParameterName()
        ];

        $this->entityManager->remove($mapping);

        $odx1 = $entityManagerOdx1->find($removedParameter->getParameterId());
        $this->entityManager->remove($name);

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

        $this->entityManager->flush();
    }

    /**
     * Change parameters order by software version
     *
     * @param EcuSwVersions $sw
     *
     * @return void
     */
    private function reOrderParametersBySw(EcuSwVersions $sw): void
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);

        $parametersMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $sw]);

        usort($parametersMapping, function ($a, $b) {
            return $a->getEcuSwParameter()->getParameterOrder() <=> $b->getEcuSwParameter()->getParameterOrder();
        });

        /* Special order for odx1. Parameters can be in wrong order after changing the order and deleting odx2 parameter */
        /* To Do: Avoid changing the order of parameters in the database table! Really? */
        $mappingOdx1 = [];
        $odx2Order = 1;
        foreach ($parametersMapping as $parameterMapping) {
            $parameter = $parameterMapping->getEcuSwParameter();

            if ($parameter->getParameterOrder() == 0) {
                continue;
            }

            $parameterId = $parameter->getEcuSwParameterId();
            $odx1 = $entityManagerOdx1->find($parameterId);

            if ($odx1) {
                if ($odx1->getIsAlsoOdx2()) {
                    $parameter->setParameterOrder($odx2Order);
                    array_push($mappingOdx1, $odx1);
                    ++$odx2Order;
                    $this->entityManager->persist($parameter);
                } else {
                    array_push($mappingOdx1, $odx1);
                }
            } else {
                $parameter->setParameterOrder($odx2Order);
                ++$odx2Order;
                $this->entityManager->persist($parameter);
            }
        }

        /* Sort to detect deleted parameters. Change the order */
        usort($mappingOdx1, function ($a, $b) {
            return $a->getSpecialOrderIdForOdx1() <=> $b->getSpecialOrderIdForOdx1();
        });

        $odx1Order = 1;

        foreach ($mappingOdx1 as $value) {
            $value->setSpecialOrderIdForOdx1($odx1Order);
            ++$odx1Order;
            $this->entityManager->persist($value);
        }

        $this->entityManager->flush();
    }

    /**
     * @param OdxCollection $collection
     *
     * @throws Exception
     */
    private function saveOdx1Parameters(OdxCollection $collection): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            foreach ($collection->getParameters() as $parameter) {
                $ecuParameter = $this->entityManager->getRepository(EcuSwParameters::class)
                    ->find($parameter->getParameterId());

                /* Name */
                switch ($parameter->getType()) {
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW:
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW:
                        $parameterName = $this->entityManager->getRepository(EcuSoftwareParameterNames::class)
                            ->findOneBy(['ecuSoftwareParameterNameId' => $parameter->getNameId()]);
                        $ecuParameter->setEcuSoftwareParameterName($parameterName);
                        break;
                    case EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER:
                        $parameterName = $this->entityManager->getRepository(EcuSoftwareParameterNames::class)
                            ->findOneBy(['ecuSoftwareParameterNameId' => $parameter->getNameId()]);

                        if ($parameterName) {
                            $ecuParameter->setEcuSoftwareParameterName($parameterName);
                        } else {
                            $parameterName = new EcuSoftwareParameterNames();
                            $parameterName->setEcuSoftwareParameterName($parameter->getName());

                            $this->entityManager->persist($parameterName);
                        }

                        $ecuParameter->setEcuSoftwareParameterName($parameterName);
                        break;
                }

                /* Values */
                if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_PARAMETER) {
                    if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT
                        || $parameter->getLinkingType() == self::LINKING_TYPE_CONSTANT
                    ) {
                        /* Clear previously values */
                        $this->clearValues($ecuParameter);

                        /* Create new one */
                        $newValue = new EcuSwParameterValuesSets();
                        $newValue->setEcuSwParameter($ecuParameter);

                        switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                            case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                                $newValue->setValueString($parameter->getValueBlob());
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                                $newValue->setValueUnsigned($parameter->getValueUnsigned());
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                            case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                            case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                                $newValue->setValueInteger($parameter->getValueInteger());
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                                $newValue->setValueBool($parameter->getValueBoolean());
                                break;
                            case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                            case VariableTypesEnum::VARIABLE_TYPE_STRING:
                                $newValue->setValueString($parameter->getValueString());
                                break;
                        }

                        if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT) {
                            $ecuParameter->setUsedDefaultValue($newValue);
                        } else {
                            $ecuParameter->setUsedConstantValue($newValue);
                        }

                        $this->entityManager->persist($newValue);
                    }
                } elseif (
                    $parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW
                    || $parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW
                ) {
                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    /* Create new one */
                    $newValue = new EcuSwParameterValuesSets();
                    $newValue->setEcuSwParameter($ecuParameter);

                    switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                        case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                            $newValue->setValueString($parameter->getValueBlob());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                            $newValue->setValueUnsigned($parameter->getValueUnsigned());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                        case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                        case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                            $newValue->setValueInteger($parameter->getValueInteger());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                            $newValue->setValueBool($parameter->getValueBoolean());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                        case VariableTypesEnum::VARIABLE_TYPE_STRING:
                            $newValue->setValueString($parameter->getValueString());
                            break;
                    }

                    if ($parameter->getType() == EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW) {
                        $ecuParameter->setUsedConstantValue($newValue);
                    } else {
                        if ($parameter->getLinkingType() == self::LINKING_TYPE_DEFAULT) {
                            $ecuParameter->setUsedDefaultValue($newValue);
                        } else {
                            $ecuParameter->setUsedConstantValue($newValue);
                        }
                    }

                    $this->entityManager->persist($newValue);
                }

                /* Link to Global Parameter */
                if ($parameter->getLinkingType() == self::LINKING_TYPE_GLOBAL_PARAMETER) {
                    $globalParameter = $this->entityManager->getRepository(GlobalParameters::class)
                        ->find($parameter->getLinkedToGlobalParameter());

                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setLinkedToGlobalParameter($globalParameter);
                }

                /* Link to Dynamic */
                if ($parameter->getLinkingType() == self::LINKING_TYPE_DYNAMIC_VALUE) {
                    $dynamicParameter = $this->entityManager
                        ->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($parameter->getDynamicParameterValuesByDiagnosticSoftware());

                    /* Clear previously values */
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setDynamicParameterValuesByDiagnosticSoftware($dynamicParameter);
                }

                /* Link to CoC */
               /* if ($parameter->getLinkingType() == self::LINKING_TYPE_COC_PARAMETER) {
                    $cocParameter = $this->entityManager
                        ->getRepository(CocParameters::class)
                        ->find($parameter->getLinkedToCoCParameter());

                    // Clear previously values
                    $this->clearValues($ecuParameter);

                    $ecuParameter->setLinkedToCocParameter($cocParameter);
                }*/

                $this->entityManager->persist($ecuParameter);
                $this->entityManager->flush();
            }

            /* Commit */
            $this->entityManager->commit();
        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    /**
     * Change parameters order with array
     *
     * @param EcuSwVersions $sw
     * @param array         $orders
     *
     * @return void
     */
    public function changeOrders(EcuSwVersions $sw, array $orders): void
    {
        $entityManager = $this->entityManager->getRepository(EcuSwParameters::class);

        foreach ($orders as $values) {
            foreach ($values as $id => $order) {
                $parameter = $entityManager->find((int)$id);
                $parameter->setParameterOrder((int)$order);

                $this->entityManager->persist($parameter);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Validate cloned Parameters
     *
     * @param $sw
     * @param $form
     *
     * @return array
     */
    public function validateClonedParameters(EcuSwVersions $sw, array $form): array
    {
        $parameterEcuSwVersionMapping = $this->manager->getRepository(EcuSwParameterEcuSwVersionMapping::class)
            ->findBy(['ecuSwVersion' => $sw]);

        $errors = [];
        foreach ($form['parameter'] as $key => $value) {
            if ($form['parameter'][$key]['previous_name'] == $form['parameter'][$key]['name']) {
                array_push($errors, "parameter[$key][name]");
            }

            if (isset($form['parameter'][$key]['previous_udsId'])
                && $form['parameter'][$key]['previous_udsId'] == $form['parameter'][$key]['udsId']) {
                 array_push($errors, "parameter[$key][udsId]");
                 continue;
            }

            if (substr(strtolower($form['parameter'][$key]['udsId']), 0, 2) == '0x') {
                array_push($errors, "parameter[$key][udsId]");
                continue;
            }

            if (!ctype_xdigit($form['parameter'][$key]['udsId'])) {
                array_push($errors, "parameter[$key][udsId]");
                continue;
            }

            if (strlen($form['parameter'][$key]['udsId']) != 4) {
                array_push($errors, "parameter[$key][udsId]");
                continue;
            }
        }

        return $errors;
    }

    /**
     * Save cloned Parameters for Sw
     *
     * @param EcuSwVersions $sw
     * @param array          $form
     *
     * @return void
     */
    public function saveClonedParameterForSwId(EcuSwVersions $sw, array $form): void
    {
        $entityManagerParameter = $this->entityManager->getRepository(EcuSwParameters::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);

        $order = $this->getMaxOrderForSwId($sw->getEcuSwVersionId());
        $odx1Iterator = 0;
        $odx2Iterator = 0;

        foreach ($form['parameter'] as $requestOrder => $value) {
            $id = $form['parameter'][$requestOrder]['id'];
            ++$odx2Iterator;
            $oldParameter = $entityManagerParameter->find($id);
            $newParameter = clone $oldParameter;

            list($default, $constant) = [
                $oldParameter->getUsedDefaultValue(),
                $oldParameter->getUsedConstantValue(),
            ];

            $oldOdx1 = $entityManagerOdx1->find($id);

            $newParameterName = new EcuSoftwareParameterNames();
            $newParameterName->setEcuSoftwareParameterName($form['parameter'][$requestOrder]['name']);
            $newParameter->setEcuSoftwareParameterName($newParameterName);
            $this->entityManager->persist($newParameterName);

            if (!is_null($oldOdx1)) {
                ++$odx1Iterator;
                $newOdx1 = new Odx1Parameters();
                $newOdx1->setOpEcuSwParameter($newParameter);
                $newOdx1->setIsAlsoOdx2($oldOdx1->getIsAlsoOdx2());
                $newOdx1->setSpecialOrderIdForOdx1($order['odx1Order'] + $odx1Iterator);
                $this->entityManager->persist($newOdx1);
            }

            if (!is_null($default)) {
                $newParameter->setUsedDefaultValue(null);
            } elseif (!is_null($constant)) {
                $newParameter->setUsedConstantValue(null);
            }

            if (isset($form['parameter'][$requestOrder]['udsId'])) {
                $udsIdSubstr = substr(strtolower($form['parameter'][$requestOrder]['udsId']), 0, 2);
                if ($udsIdSubstr == "0x") {
                    $newParameter->setDataIdentifier($form['parameter'][$requestOrder]['udsId']);
                } else {
                    $newParameter->setDataIdentifier("0x" . $form['parameter'][$requestOrder]['udsId']);
                }
            }

            $newParameter->setParameterOrder($order['odx2Order'] + $odx2Iterator);
            $this->entityManager->persist($newParameter);

            if (!is_null($default)) {
                $newValueSet = clone $default;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedDefaultValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            } elseif (!is_null($constant)) {
                $newValueSet = clone $constant;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedConstantValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            }

            $parameterSwMapping = new EcuSwParameterEcuSwVersionMapping();
            $parameterSwMapping->setEcuSwParameter($newParameter);
            $parameterSwMapping->setEcuSwVersion($sw);
            $this->entityManager->persist($parameterSwMapping);
        }

        $this->entityManager->flush();
    }

    /**
     * Get max order odx1 and odx2 for sw
     *
     * @param int $sw
     *
     * @return array
     */
    private function getMaxOrderForSwId(int $sw): array
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);
        $entityManagerParameter = $this->entityManager->getRepository(EcuSwParameters::class);

        $parametersMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $sw]);

        $ids = [];
        foreach ($parametersMapping as $parameterMap) {
            array_push($ids, $parameterMap->getEcuSwParameter()->getEcuSwParameterId());
        }
        $parameterOdx2 = $entityManagerParameter->findBy(['ecuSwParameterId' => $ids], ['parameterOrder' => 'DESC'], 1);
        $odx2Order = $parameterOdx2[0]->getParameterOrder();

        $parameterOdx1 = $entityManagerOdx1->findBy(['opEcuSwParameter' => $ids], ['specialOrderIdForOdx1' => 'DESC'], 1);
        $odx1Order = $parameterOdx1[0]->getSpecialOrderIdForOdx1();

        return ['odx1Order' => $odx1Order, 'odx2Order' => $odx2Order];
    }

    /**
     * Check conflicts for selected parameters
     *
     * @param int $swCurrent
     * @param int $swDestination
     * @param array $parameters
     *
     * @return array
     */
    public function checkConflicts(int $swCurrent, int $swDestination, array $parameters): array
    {
        $conflicts = [];
        $set = [];

        $entityManagerSW = $this->entityManager->getRepository(EcuSwVersions::class);
        $entityManagerParameter = $this->entityManager->getRepository(EcuSwParameters::class);
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);

        $checkUdsCurrent = ($entityManagerSW->findOneBy(['ecuSwVersionId' => $swCurrent])
                ->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName() != 'XCP') ? 1 : 0;
        $checkUdsDestination = ($entityManagerSW->findOneBy(['ecuSwVersionId' => $swDestination])
                ->getEcuCommunicationProtocol()->getEcuCommunicationProtocolName() != 'XCP') ? 1 : 0;

        $currentParameters = $entityManagerParameter->findBy(['ecuSwParameterId' => $parameters]);
        $destinationParametersMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $swDestination]);
        $destinationParameters = array_map(function ($element) {
            return $element->getEcuSwParameter();
        }, $destinationParametersMapping);

        $onlyOdx1Parameters = $entityManagerOdx1->findBy(['opEcuSwParameter' => $destinationParameters]);
        $onlyOdx1Parameters = array_filter($onlyOdx1Parameters, function ($element) {
            return $element->getIsAlsoOdx2() == false;
        });
        $onlyOdx1Parameters = array_map(function ($element) {
            return $element->getEcuSwParameter()->getEcuSwParameterId();
        }, $onlyOdx1Parameters);

        $destinationParameters = array_filter($destinationParameters, function ($element) use ($onlyOdx1Parameters) {
            return $element->getEcuSwParameterType()->getEcuSwParameterTypeId() == 4
                && !in_array($element->getEcuSwParameterId(), $onlyOdx1Parameters);
        });


        foreach ($currentParameters as $currentKey => $currentParameter) {
            foreach ($destinationParameters as $destinationKey => $destinationParameter) {
                /* Check UdsId */
                if ($checkUdsCurrent == $checkUdsDestination
                    && $currentParameter->getDataIdentifier() == $destinationParameter->getDataIdentifier()
                    && !(isset($set[$destinationParameter->getEcuSwParameterId()]))) {
                    array_push($conflicts, [
                        'ecu_parameter_id_destination' => $destinationParameter->getEcuSwParameterId(),
                        'ecu_parameter_name_destination' => $destinationParameter->getEcuSoftwareParameterName()
                            ->getEcuSoftwareParameterName(),
                        'ecu_parameter_id_current' => $currentParameter->getEcuswParameterId(),
                        'ecu_parameter_name_current' => $currentParameter->getEcuSoftwareParameterName()
                            ->getEcuSoftwareParameterName()
                    ]);
                    $set[$destinationParameter->getEcuSwParameterId()] = 1;
                    continue;
                }
                if ($currentParameter->getEcuSoftwareParameterName()->getEcuSoftwareParameterName()
                    == $destinationParameter->getEcuSoftwareParameterName()->getEcuSoftwareParameterName()
                    && !(isset($set[$destinationParameter->getEcuSwParameterId()]))) {
                    array_push($conflicts, [
                        'ecu_parameter_id_destination' => $destinationParameter->getEcuSwParameterId(),
                        'ecu_parameter_name_destination' => $destinationParameter->getEcuSoftwareParameterName()
                            ->getEcuSoftwareParameterName(),
                        'ecu_parameter_id_current' => $currentParameter->getEcuswParameterId(),
                        'ecu_parameter_name_current' => $currentParameter->getEcuSoftwareParameterName()
                            ->getEcuSoftwareParameterName()
                    ]);
                    $set[$destinationParameter->getEcuSwParameterId()] = 1;
                    continue;
                }
            }
        }

        return !(empty($conflicts)) ? $conflicts : ['empty'];
    }

    /**
     * Resolve conflicts for selected parameters and sw
     *
     * @param EcuSwVersions $swDestination
     * @param array         $parametersConflict
     * @param array         $parametersWConflict
     *
     * @return string
     */
    public function resolveConflicts(
        EcuSwVersions $swDestination,
        array $parametersConflict,
        array $parametersWConflict
    ): string
    {
        $entityManagerParameter = $this->entityManager->getRepository(EcuSwParameters::class);
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);


        $suffix = ($swDestination->getSuffixIfIsSubEcuSwVersion()) ? ' ---> ' .
            $swDestination->getSuffixIfIsSubEcuSwVersion() : '';
        $swName = $swDestination->getSwVersion() . $suffix;

        if (!(empty($parametersWConflict)))
            $this->copyWithoutConflict($swDestination, $parametersWConflict);

        $order = $this->getMaxOrderForSwId($swDestination->getEcuSwVersionId());
        $odx1Iterator = 0;

        foreach ($parametersConflict as $parameterMap) {
            $currentParameter = $entityManagerParameter->find($parameterMap['current']);
            $destinationParameter = $entityManagerParameter->find($parameterMap['destination']);
            $destinationMapping = $entityManagerMapping->findOneBy(['ecuSwVersion' => $swDestination,
                'ecuSwParameter' => $parameterMap['destination']]);
            $currentOdx1 = $entityManagerOdx1->find($parameterMap['current']);
            $destinationOdx1 = $entityManagerOdx1->find($parameterMap['destination']);
            $newParameter = clone $currentParameter;


            list($default, $constant) = [
                $currentParameter->getUsedDefaultValue(),
                $currentParameter->getUsedConstantValue(),
            ];

            if (!is_null($default)) {
                $newParameter->setUsedDefaultValue(null);
            } elseif (!is_null($constant)) {
                $newParameter->setUsedConstantValue(null);
            }

            $newParameter->setParameterOrder($destinationParameter->getParameterOrder());
            $this->entityManager->remove($destinationMapping);
            $this->entityManager->remove($destinationParameter);

            $newParameterName = new EcuSoftwareParameterNames();
            $newParameterName->setEcuSoftwareParameterName($currentParameter->getEcuSoftwareParameterName()->getEcuSoftwareParameterName());
            $newParameter->setEcuSoftwareParameterName($newParameterName);
            $this->entityManager->persist($newParameterName);

            $this->entityManager->persist($newParameter);

            if (!is_null($default)) {
                $newValueSet = clone $default;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedDefaultValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            } elseif (!is_null($constant)) {
                $newValueSet = clone $constant;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedConstantValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            }

            $destinationOdx1Order = null;
            if (!is_null($destinationOdx1)) {
                $destinationOdx1Order = $destinationOdx1->getSpecialOrderIdForODx1();
                $this->entityManager->remove($destinationOdx1);
            }

            if (!is_null($currentOdx1)) {
                $newOdx1 = new Odx1Parameters();
                $newOdx1->setOpEcuSwParameter($newParameter);
                $newOdx1->setIsAlsoOdx2($currentOdx1->getIsAlsoOdx2());
                $newOdx1->setSpecialOrderIdForOdx1($destinationOdx1Order
                    ?? ($order['odx1Order'] + (++$odx1Iterator)));
                $this->entityManager->persist($newOdx1);
            }

            $parameterSwMapping = new EcuSwParameterEcuSwVersionMapping();
            $parameterSwMapping->setEcuSwParameter($newParameter);
            $parameterSwMapping->setEcuSwVersion($swDestination);
            $this->entityManager->persist($parameterSwMapping);
        }

        $this->entityManager->flush();

        return $swName;
    }

    /**
     * Copy parameter to other sw without conflict
     *
     * @param EcuSwVersions $swDestination
     * @param array         $parametersWConflict
     *
     * @return string
     */
    public function copyWithoutConflict(EcuSwVersions $swDestination, array $parametersWConflict): string
    {
        $entityManagerParameter = $this->entityManager->getRepository(EcuSwParameters::class);
        $entityManagerOdx1 = $this->entityManager->getRepository(Odx1Parameters::class);

        $suffix = ($swDestination->getSuffixIfIsSubEcuSwVersion()) ? ' ---> ' .
            $swDestination->getSuffixIfIsSubEcuSwVersion() : '';
        $swName = $swDestination->getSwVersion() . $suffix;

        $order = $this->getMaxOrderForSwId($swDestination->getEcuSwVersionId());
        $odx1Iterator = 0;
        $odx2Iterator = 0;

        foreach ($parametersWConflict as $parameterId) {
            ++$odx2Iterator;
            $currentParameter = $entityManagerParameter->find($parameterId);
            $newParameter = clone $currentParameter;

            list($default, $constant) = [
                $currentParameter->getUsedDefaultValue(),
                $currentParameter->getUsedConstantValue(),
            ];

            $currentOdx1 = $entityManagerOdx1->find($parameterId);

            if (!is_null($currentOdx1)) {
                ++$odx1Iterator;
                $newOdx1 = new Odx1Parameters();
                $newOdx1->setOpEcuSwParameter($newParameter);
                $newOdx1->setIsAlsoOdx2($currentOdx1->getIsAlsoOdx2());
                $newOdx1->setSpecialOrderIdForOdx1($order['odx1Order'] + $odx1Iterator);
                $this->entityManager->persist($newOdx1);
            }

            if (!is_null($default)) {
                $newParameter->setUsedDefaultValue(null);
            } elseif (!is_null($constant)) {
                $newParameter->setUsedConstantValue(null);
            }

            $newParameterName = new EcuSoftwareParameterNames();
            $newParameterName->setEcuSoftwareParameterName($currentParameter->getEcuSoftwareParameterName()->getEcuSoftwareParameterName());
            $newParameter->setEcuSoftwareParameterName($newParameterName);
            $this->entityManager->persist($newParameterName);

            $newParameter->setParameterOrder($order['odx2Order'] + $odx2Iterator);
            $this->entityManager->persist($newParameter);

            if (!is_null($default)) {
                $newValueSet = clone $default;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedDefaultValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            } elseif (!is_null($constant)) {
                $newValueSet = clone $constant;
                $newValueSet->setEcuSwParameter($newParameter);
                $newParameter->setUsedConstantValue($newValueSet);
                $this->entityManager->persist($newValueSet);
            }

            $parameterSwMapping = new EcuSwParameterEcuSwVersionMapping();
            $parameterSwMapping->setEcuSwParameter($newParameter);
            $parameterSwMapping->setEcuSwVersion($swDestination);
            $this->entityManager->persist($parameterSwMapping);
        }

        $this->entityManager->flush();

        return $swName;
    }
}