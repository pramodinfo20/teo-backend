<?php


namespace App\Service\Configuration;


use App\Converter\ASCIICdisStrategy;
use App\Converter\ASCIIStrategy;
use App\Converter\BlobStrategy;
use App\Converter\BoolStrategy;
use App\Converter\IntegerStrategy;
use App\Converter\StringStrategy;
use App\Converter\UnsignedStrategy;
use App\Entity\CocParameters;
use App\Entity\CocParameterValuesSetsMapping;
use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Entity\EcuCommunicationProtocols;
use App\Entity\EcuSwParameterEcuSwVersionMapping;
use App\Entity\EcuSwParameterEcuSwVersionMappingOverwrite;
use App\Entity\EcuSwParameters;
use App\Entity\EcuSwParameterValuesSets;
use App\Entity\EcuSwVersionGeneralProperties;
use App\Entity\EcuSwVersions;
use App\Entity\GlobalParameters;
use App\Entity\GlobalParameterValuesSetsMapping;
use App\Entity\SubVehicleConfigurations;
use App\Enum\Entity\ConfigurationEcus as EcusConstants;
use App\Enum\Entity\EcuSwParameterTypes as EcuSwParameterTypesEnum;
use App\Enum\Entity\VariableTypes as VariableTypesEnum;
use App\Model\Configuration\Odx1Collection;
use App\Model\Configuration\Odx1Parameter;
use App\Model\Configuration\Odx2Collection;
use App\Model\Configuration\Odx2Parameter;
use App\Model\Configuration\OdxCollection;
use App\Service\History\Configuration\HistoryParameterI;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Variable;

class Parameter implements HistoryParameterI
{
    const LINKING_TYPE_DEFAULT = 1;
    const LINKING_TYPE_CONSTANT = 2;
    const LINKING_TYPE_GLOBAL_PARAMETER = 3;
    const LINKING_TYPE_DYNAMIC_VALUE = 4;
//    const LINKING_TYPE_COC_PARAMETER = 5;

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
/*            case Parameter::LINKING_TYPE_COC_PARAMETER:
                return 'CoC';*/
        }
    }

    /**
     * @param int                      $odx
     * @param string                   $headerProtocol
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subconf
     *
     * @return Odx2Collection
     */
    public function getParameters(int $odx, string $headerProtocol, EcuSwVersions $sw, SubVehicleConfigurations $subconf): OdxCollection
    {

        if ($odx == 1) {
            return $this->getOdx1Parameters($sw, $subconf);
        } else {
            return $this->getOdx2Parameters($sw, $subconf, $headerProtocol);
        }
    }

    /**
     * Retrieve parameters for ODX1 and set it in model
     *
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subconf
     *
     * @return Odx1Collection
     */
    private function getOdx1Parameters(EcuSwVersions $sw, SubVehicleConfigurations $subconf): Odx1Collection
    {
        $cocValueSettMappingManager = $this->manager->getRepository(CocParameterValuesSetsMapping::class);
        $globalValueSettMappingManager = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class);

        $parametersCollection = new Odx1Collection();

        $parameters = $this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMapping::class)
            ->getParametersOdx1BySwId($sw);

        $overwrittenParameters = new ArrayCollection($this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMappingOverwrite::class)
            ->getOverwrittenParametersForOdx1BySwIdAndSubconfId($sw, $subconf));

        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $parameter) {
                $overwrittenParameter = $overwrittenParameters->filter(function ($param) use ($parameter)
                {
                    return $param['overwritten_parameter_id'] == $parameter['parameter_id'];
                });

                $odx1Parameter = new Odx1Parameter();
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

                if ($overwrittenParameter->isEmpty()) {
                    $odx1Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                            $parameter['value_string'] : null));
                    $odx1Parameter->setValueBool($parameter['value_bool']);
                    $odx1Parameter->setValueInteger($parameter['value_integer']);
                    $odx1Parameter->setValueUnsigned($parameter['value_unsigned']);
                    if ((Parameter::getLinkingTypeByName($parameter['linking_type']) == 3)) {
                        $hex = $globalValueSettMappingManager
                            ->findHexByGlobalIdAndSubConfId($parameter['global_parameter_id'],
                                $subconf->getSubVehicleConfigurationId())['valueHex'];
                        $odx1Parameter->setValueHex((!is_null($hex)) ?
                            (($hex != 'DUMMY_VALUE') ?
                                substr($hex, 2) : null) :
                            $hex);
     /*               } else if (Parameter::getLinkingTypeByName($parameter['linking_type']) == 5) {
                        $hex = $cocValueSettMappingManager
                            ->findHexByCocIdAndSubConfId($parameter['coc_parameter_id'],
                                $subconf->getSubVehicleConfigurationId())['valueHex'];
                        $odx1Parameter->setValueHex((!is_null($hex)) ?
                            (($hex != 'DUMMY_VALUE') ?
                                substr($hex, 2) : null) :
                            $hex);*/
                    } else {
                        $odx1Parameter->setValueHex((!is_null($parameter['value_hex'])) ?
                            (($parameter['value_hex'] != 'DUMMY_VALUE') ?
                                substr($parameter['value_hex'], 2) : null) :
                            $parameter['value_hex']);
                    }
                    $odx1Parameter->setOverwrittenValueSetId(null);
                } else {
                    $odx1Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                            $overwrittenParameter->first()['overwritten_value_string'] : null));
                    $odx1Parameter->setValueBool($overwrittenParameter->first()['overwritten_value_bool']);
                    $odx1Parameter->setValueInteger($overwrittenParameter->first()['overwritten_value_integer']);
                    $odx1Parameter->setValueUnsigned($overwrittenParameter->first()['overwritten_value_unsigned']);
                    $odx1Parameter->setValueHex((!is_null($overwrittenParameter->first()['overwritten_value_hex'])) ?
                        (($overwrittenParameter->first()['overwritten_value_hex'] != 'DUMMY_VALUE') ?
                            substr($overwrittenParameter->first()['overwritten_value_hex'], 2) : null) :
                        $overwrittenParameter->first()['overwritten_value_hex']);
                    $odx1Parameter->setOverwrittenValueSetId($overwrittenParameter->first()['overwritten_value_set_id']);
                }

                $odx1Parameter->setLinkingTypeId(Parameter::getLinkingTypeByName($parameter['linking_type']));
                $odx1Parameter->setLinkingType($parameter['linking_type']);
                $odx1Parameter->setParameterId($parameter['parameter_id']);
                $odx1Parameter->setVariableTypeId($parameter['type_id']);
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

      /*          if (isset($parameter['coc_parameter_id'])) {
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
/*            case 'CoC':
                return Parameter::LINKING_TYPE_COC_PARAMETER;*/
        }
    }

    /**
     * Retrieve parameters for ODX2 and set it in model
     *
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subconf
     * @param                          $headerProtocol
     *
     * @return Odx2Collection
     */
    private function getOdx2Parameters(EcuSwVersions $sw, SubVehicleConfigurations $subconf, $headerProtocol): Odx2Collection
    {
        $cocValueSettMappingManager = $this->manager->getRepository(CocParameterValuesSetsMapping::class);
        $globalValueSettMappingManager = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class);

        $parametersCollection = new Odx2Collection();

        $parameters = $this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMapping::class)
            ->getParametersOdx2BySwId($sw);


        $overwrittenParameters = new ArrayCollection($this->manager
            ->getRepository(EcuSwParameterEcuSwVersionMappingOverwrite::class)
            ->getOverwrittenParametersForOdx2BySwIdAndSubconfId($sw, $subconf));

        if (is_array($parameters) && count($parameters)) {
            foreach ($parameters as $parameter) {

                $overwrittenParameter = $overwrittenParameters->filter(function ($param) use ($parameter)
                {
                    return $param['overwritten_parameter_id'] == $parameter['parameter_id'];
                });

                $odx2Parameter = new Odx2Parameter();
                $odx2Parameter->setParameterId($parameter['parameter_id']);
                $odx2Parameter->setCoding($parameter['coding']);
                $odx2Parameter->setBigEndian($parameter['isBigEndian']);
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
                if ($overwrittenParameter->isEmpty()) {
                    if (VariableTypesEnum::getVariableTypeByName($parameter['type']) ==
                        VariableTypesEnum::VARIABLE_TYPE_BLOB) {
                        $odx2Parameter->setValueBlob((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                          $parameter['value_string'] : null));
                    } else {
                        $odx2Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                            $parameter['value_string'] : null));
                    }
                    $odx2Parameter->setValueBool($parameter['value_bool']);
                    $odx2Parameter->setValueInteger($parameter['value_integer']);
                    $odx2Parameter->setValueUnsigned($parameter['value_unsigned']);
                    if ((Parameter::getLinkingTypeByName($parameter['linking_type']) == 3)) {
                        $hex = $globalValueSettMappingManager
                            ->findHexByGlobalIdAndSubConfId($parameter['global_parameter_id'],
                                $subconf->getSubVehicleConfigurationId())['valueHex'];
                         $odx2Parameter->setValueHex((!is_null($hex)) ?
                             (($hex != 'DUMMY_VALUE') ?
                                 substr($hex, 2) : null) :
                             $hex);
/*                    } else if (Parameter::getLinkingTypeByName($parameter['linking_type']) == 5) {
                        $hex = $cocValueSettMappingManager
                            ->findHexByCocIdAndSubConfId($parameter['coc_parameter_id'],
                                $subconf->getSubVehicleConfigurationId())['valueHex'];
                        $odx2Parameter->setValueHex((!is_null($hex)) ?
                            (($hex != 'DUMMY_VALUE') ?
                                substr($hex, 2) : null) :
                            $hex);*/
                    } else {
                        $odx2Parameter->setValueHex((!is_null($parameter['value_hex'])) ?
                            (($parameter['value_hex'] != 'DUMMY_VALUE') ?
                                substr($parameter['value_hex'], 2) : null) :
                            $parameter['value_hex']);
                    }
                    $odx2Parameter->setOverwrittenValueSetId(null);
                } else {
                    if (VariableTypesEnum::getVariableTypeByName($parameter['type']) ==
                        VariableTypesEnum::VARIABLE_TYPE_BLOB) {
                        $odx2Parameter->setValueBlob((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                          $overwrittenParameter->first()['overwritten_value_string'] : null));
                    } else {
                        $odx2Parameter->setValueString((in_array(Parameter::getLinkingTypeByName($parameter['linking_type']), [1, 2]) ?
                            $overwrittenParameter->first()['overwritten_value_string'] : null));
                    }
                    $odx2Parameter->setValueBool($overwrittenParameter->first()['overwritten_value_bool']);
                    $odx2Parameter->setValueInteger($overwrittenParameter->first()['overwritten_value_integer']);
                    $odx2Parameter->setValueUnsigned($overwrittenParameter->first()['overwritten_value_unsigned']);
                    $odx2Parameter->setValueHex((!is_null($overwrittenParameter->first()['overwritten_value_hex'])) ?
                        (($overwrittenParameter->first()['overwritten_value_hex'] != 'DUMMY_VALUE') ?
                            substr($overwrittenParameter->first()['overwritten_value_hex'], 2) : null) :
                        $overwrittenParameter->first()['overwritten_value_hex']);
                    $odx2Parameter->setOverwrittenValueSetId($overwrittenParameter->first()['overwritten_value_set_id']);
                }

                $odx2Parameter->setVariableTypeId($parameter['type_id']);
                $odx2Parameter->setLinkingType(Parameter::getLinkingTypeByName($parameter['linking_type']));
                $odx2Parameter->setLinkingTypeName($parameter['linking_type']);
                $odx2Parameter->setStartBit($parameter['start_bit']);
                $odx2Parameter->setStopBit($parameter['stop_bit']);
                $odx2Parameter->setUdsId((!is_null($parameter['uds_id'])) ? (($parameter['uds_id'] != 'DUMMY_VALUE') ?
                    substr($parameter['uds_id'], 2) : null) : $parameter['uds_id']);
                $odx2Parameter->setSerialState($parameter['serial_state']);
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

   /*             if (isset($parameter['coc_parameter_id'])) {
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
     * @param OdxCollection            $collection
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subConf
     * @param int                      $odx
     *
     * @throws Exception
     */
    public function save(OdxCollection $collection, EcuSwVersions $sw, SubVehicleConfigurations $subConf, int $odx): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            $valueSetManager = $this->entityManager->getRepository(EcuSwParameterValuesSets::class);

            foreach ($collection->getParameters() as $parameter) {
                $ecuParameter = $this->entityManager->getRepository(EcuSwParameters::class)
                    ->find($parameter->getParameterId());


                /* Values */
                if ($parameter->getType() != EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SERIAL) {
                    if ((($odx == 2) ? $parameter->getLinkingType() : $parameter->getLinkingTypeId()) == Parameter::LINKING_TYPE_DEFAULT) {
                        if (!is_null($parameter->getoverwrittenValueSetId())) {
                            $valueSet = $valueSetManager->find($parameter->getoverwrittenValueSetId());
                            $valueSet->setValueBool(null);
                            $valueSet->setValueHex(null);
                            $valueSet->setValueInteger(null);
                            $valueSet->setValueString(null);
                            $valueSet->setValueUnsigned(null);


                            switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                                case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                                    $valueSet->setValueString($parameter->getValueBlob());
                                    ($odx == 2) ? $valueSet->setValueHex(
                                        (new BlobStrategy())->convertToHex((string)$parameter->getValueBlob(),
                                            $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                        )) : '';
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                                    $valueSet->setValueUnsigned($parameter->getValueUnsigned());
                                    ($odx == 2) ? $valueSet->setValueHex(
                                        (new UnsignedStrategy())->convertToHex((string)$parameter->getValueUnsigned(),
                                            $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                        )) : '';
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                                case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                                case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                                $valueSet->setValueInteger($parameter->getValueInteger());
                                    ($odx == 2) ? $valueSet->setValueHex(
                                        (new IntegerStrategy())->convertToHex((string)$parameter->getValueInteger(),
                                            $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                        )) : '';
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                                    $valueSet->setValueBool($parameter->isValueBool())
                                    ($odx == 2) ? $valueSet->setValueHex(
                                        (new BoolStrategy())->convertToHex((string)$parameter->isValueBool(),
                                            $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                        )) : '';
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                                    $valueSet->setValueString($parameter->getValueString());
                                    if ($sw->getCeEcu()->getEcuName() == EcusConstants::CDIS_NAME && !in_array
                                        ($parameter->getType(),
                                            [
                                                EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW,
                                                EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW
                                            ]
                                        )) {
                                        ($odx == 2) ? $valueSet->setValueHex(
                                            (new ASCIICdisStrategy())->convertToHex((string)$parameter->getValueString(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                    } else {
                                        ($odx == 2) ? $valueSet->setValueHex(
                                            (new ASCIIStrategy())->convertToHex((string)$parameter->getValueString(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                    }

                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_STRING:
                                    $valueSet->setValueString($parameter->getValueString());
                                    ($odx == 2) ? $valueSet->setValueHex(
                                        (new StringStrategy())->convertToHex((string)$parameter->getValueString(),
                                            $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                        )) : '';
                                    break;
                            }

                            $this->entityManager->persist($valueSet);
                        } else {
                            $valueChanged = true;

                            $parameterType = VariableTypesEnum::getVariableTypeByName($parameter->getVariableType());
                            $valueSet = $ecuParameter->getUsedDefaultValue();

                            switch ($parameterType) {
                                case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                                case VariableTypesEnum::VARIABLE_TYPE_STRING:
                                case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                                    if ($parameter->getValueString() == $valueSet->getValueString())
                                        $valueChanged = false;
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                                    if ($parameter->getValueUnsigned() == $valueSet->getValueUnsigned())
                                        $valueChanged = false;
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                                case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                                case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                                    if ($parameter->getValueInteger() == $valueSet->getValueInteger())
                                        $valueChanged = false;
                                    break;
                                case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                                    if ($parameter->getValueBool() == $valueSet->getValueBool())
                                        $valueChanged = false;
                                    break;
                            }


                            if ($valueChanged) {

                                $newValueSet = new EcuSwParameterValuesSets();
                                $newValueSet->setEcuSwParameter($ecuParameter);

                                switch (VariableTypesEnum::getVariableTypeByName($parameter->getVariableType())) {
                                    case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                                        $newValueSet->setValueString($parameter->getValueBlob());
                                        ($odx == 2) ? $newValueSet->setValueHex(
                                            (new BlobStrategy())->convertToHex((string)$parameter->getValueBlob(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                        break;
                                    case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                                        $newValueSet->setValueUnsigned($parameter->getValueUnsigned());
                                        ($odx == 2) ? $newValueSet->setValueHex(
                                            (new UnsignedStrategy())->convertToHex((string)$parameter->getValueUnsigned(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                        break;
                                    case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                                    case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                                    case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                                        $newValueSet->setValueInteger($parameter->getValueInteger());
                                        ($odx == 2) ? $newValueSet->setValueHex(
                                            (new IntegerStrategy())->convertToHex((string)$parameter->getValueInteger(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                        break;
                                    case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                                        $newValueSet->setValueBool($parameter->isValueBool())
                                        ($odx == 2) ? $newValueSet->setValueHex(
                                            (new BoolStrategy())->convertToHex((string)$parameter->isValueBool(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                        break;
                                    case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                                        $newValueSet->setValueString($parameter->getValueString());
                                        if ($sw->getCeEcu()->getEcuName() == EcusConstants::CDIS_NAME
                                             && !in_array($parameter->getType(),
                                                [
                                                    EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_HW,
                                                    EcuSwParameterTypesEnum::ECU_PARAMETER_TYPE_SW
                                                ]
                                            )) {
                                            ($odx == 2) ? $valueSet->setValueHex(
                                                (new ASCIICdisStrategy())->convertToHex((string)$parameter->getValueString(),
                                                    $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                                )) : '';
                                        } else {
                                            ($odx == 2) ? $valueSet->setValueHex(
                                                (new ASCIIStrategy())->convertToHex((string)$parameter->getValueString(),
                                                    $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                                )) : '';
                                        }
                                        break;
                                    case VariableTypesEnum::VARIABLE_TYPE_STRING:
                                        $newValueSet->setValueString($parameter->getValueString());
                                        ($odx == 2) ? $newValueSet->setValueHex(
                                            (new StringStrategy())->convertToHex((string)$parameter->getValueString(),
                                                $parameter->getBytes(), $ecuParameter->getIsBigEndian()
                                            )) : '';
                                        break;
                                }

                                $this->entityManager->persist($newValueSet);

                                $mappingOverwrite = new EcuSwParameterEcuSwVersionMappingOverwrite();

                                $mappingOverwrite->setEcuSwVersion($sw);
                                $mappingOverwrite->setSubVehicleConfiguration($subConf);
                                $mappingOverwrite->setEcuSwParameterValueSet($newValueSet);

                                $this->entityManager->persist($mappingOverwrite);
                            }
                        }
                    }
                }

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
}