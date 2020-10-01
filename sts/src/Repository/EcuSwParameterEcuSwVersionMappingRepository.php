<?php

namespace App\Repository;

use App\Entity\EcuSwParameterEcuSwVersionMapping;
use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwParameterEcuSwVersionMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwParameterEcuSwVersionMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwParameterEcuSwVersionMapping[]    findAll()
 * @method EcuSwParameterEcuSwVersionMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset= null)
 */
class EcuSwParameterEcuSwVersionMappingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwParameterEcuSwVersionMapping::class);
    }


    public function getParametersOdx2BySwId(EcuSwVersions $sw)
    {
        return $this->createQueryBuilder('swm')
            /* --- Linked Values, Types --- */
            // Type
            ->addSelect('CASE 
                    WHEN pa.linkedToGlobalParameter IS NOT NULL THEN gpvt.variableTypeName
                    ELSE vt.variableTypeName END as type
                ')
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN cocvt.variableTypeName
            // Value -> Name for Dynamic, Global, Coc
            ->addSelect('CASE
                    WHEN pa.linkedToGlobalParameter IS NOT NULL THEN gp.globalParameterName
                    WHEN pa.dynamicParameterValuesByDiagnosticSoftware IS NOT NULL THEN dv.dynamicParameterValuesByDiagnosticSoftwareName
                    WHEN dvs.valueString IS NOT NULL  THEN dvs.valueString
                    WHEN cvs.valueString IS NOT NULL THEN cvs.valueString
                    ELSE :null
                    END as value_string
                ')
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN coc.cocParameterName
            ->addSelect('CASE

                        WHEN dvs.valueBool IS NOT NULL  THEN dvs.valueBool
                        WHEN cvs.valueBool IS NOT NULL THEN cvs.valueBool
                        ELSE :null
                        END as value_bool
                    ')
            ->addSelect('CASE

                        WHEN dvs.valueInteger IS NOT NULL  THEN dvs.valueInteger
                        WHEN cvs.valueInteger IS NOT NULL THEN cvs.valueInteger
                        ELSE :null
                        END as value_integer
                    ')
            ->addSelect('CASE

                        WHEN dvs.valueUnsigned IS NOT NULL  THEN dvs.valueUnsigned
                        WHEN cvs.valueUnsigned IS NOT NULL THEN cvs.valueUnsigned
                        ELSE :null
                        END as value_unsigned
                    ')
            // Factor
            ->addSelect(' pa.factor as factor')
            // Offset
            ->addSelect(' pa.parameterOffset as offset')
            /* Linking Type */
            ->addSelect("CASE
                WHEN pa.linkedToGlobalParameter IS NOT NULL THEN 'Global Parameter'
                WHEN pa.dynamicParameterValuesByDiagnosticSoftware IS NOT NULL THEN 'Dynamic'
                WHEN pa.usedDefaultValue IS NOT NULL THEN 'Default'
                WHEN pa.usedConstantValue IS NOT NULL THEN 'Constant'
                ELSE 'Undefined'
                END as linking_type")
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN 'CoC'
            /* --- Protocol For UDS + XCP Type --- */
            ->addSelect('CASE WHEN pa.ecuCommunicationProtocol IS NOT NULL THEN cp.ecuCommunicationProtocolName ELSE :null END as protocol')
            /* ----------------------------------- */
            /* --- R/W/C --- */
            ->addSelect('CASE WHEN pa.shouldReadParameterValueFromEcu = true THEN :R ELSE :empty END as read')
            ->addSelect('CASE WHEN pa.shouldWriteParameterValueToEcu = true THEN :W ELSE :empty END as write')
            ->addSelect('CASE WHEN pa.shouldConfirmParameterValueFromEcu = true THEN :C ELSE :empty END as confirm')
            /* ------------- */
            /* --- Support odx1 --- */
            ->addSelect("CASE WHEN odx.opEcuSwParameter IS NOT NULL THEN 'true' ELSE 'false' END as odx1")
            /* -------------------- */
            ->addSelect('pa.startBit as start_bit', 'pa.stopBit as stop_bit', 'pa.numberOfBytes as bytes',
                'pa.parameterOrder as order', 'u.unitName as unit', 'pn.ecuSoftwareParameterName as name', 'pa.dataIdentifier as uds_id',
                'pa.ecuSwParameterId as parameter_id', 'IDENTITY(pa.linkedToGlobalParameter) AS global_parameter_id',
                'IDENTITY(pa.dynamicParameterValuesByDiagnosticSoftware) as dynamic_value_id',
                'IDENTITY(pa.linkedToCocParameter) as coc_parameter_id', 'pn.ecuSoftwareParameterNameId as name_id',
                'pa.activated as active'
            )
            /* --- Support odx1 --- */
            ->addSelect("CASE WHEN st.serialState = 'true' THEN 1 ELSE 0 END as serial_state")
            /* ------------- */
            /* --- Type Order --- */
            ->addSelect("CASE
                 WHEN pt.parameterType = 'HW' THEN 1 
                 WHEN pt.parameterType = 'SW' THEN 2
                 WHEN pt.parameterType = 'Serial' THEN 3
                 ELSE 4 END as type_order    
             ")
            /* -------- coding - new column ----- */
            ->addSelect("pa.coding as coding")
            /* -------- isBigEndian - new column ----- */
            ->addSelect("pa.isBigEndian")
            /* -------------- HEX VALUES FOR 5th IT ---------------- */
            ->addSelect("CASE
                WHEN dvs.valueHex IS NOT NULL THEN dvs.valueHex
                WHEN cvs.valueHex IS NOT NULL THEN cvs.valueHex
                ELSE :null
                END as value_hex
            ")
            /* ------------- VARIABLE TYPE ID FOR 5th IT ----------- */
            ->addSelect('vt.variableTypeId as type_id')
            /* -------------------- */
            ->join('App:EcuSwParameters', 'pa', 'WITH',
                'swm.ecuSwParameter = pa.ecuSwParameterId')
            ->join('App:EcuSwParameterTypes', 'pt', 'WITH',
                'pa.ecuSwParameterType = pt.ecuSwParameterTypeId')
            ->leftjoin('App:EcuSwParameterSerialStates', 'st', 'WITH',
                'pa.ecuSwParameterId = st.ecuSwParameter')
            ->leftjoin('App:Odx1Parameters', 'odx', 'WITH',
                'pa.ecuSwParameterId = odx.opEcuSwParameter')
            /* --- Join to parameters --- */
            ->join('App:VariableTypes', 'vt', 'WITH',
                'pa.variableType = vt.variableTypeId')
            /* --- Linking and Values --- */
            /* --- Global Parameters --- */
            ->leftjoin('App:GlobalParameters', 'gp', 'WITH',
                'pa.linkedToGlobalParameter = gp.globalParameterId')
            ->leftjoin('App:VariableTypes', 'gpvt', 'WITH',
                'gp.variableType = gpvt.variableTypeId')
            /* ------------------------- */
            /* --- COC Parameters --- */
            ->leftjoin('App:CocParameters', 'coc', 'WITH',
                'pa.linkedToCocParameter = coc.cocParameterId')
            ->leftjoin('App:VariableTypes', 'cocvt', 'WITH',
                'coc.variableType = cocvt.variableTypeId')
            /* ---------------------- */
            /* --- Dynamic Parameters --- */
            ->leftjoin('App:DynamicParameterValuesByDiagnosticSoftware', 'dv', 'WITH',
                'pa.dynamicParameterValuesByDiagnosticSoftware = dv.dpvbdsId')
            /* -------------------------- */
            /* --- Default and Constant Parameters --- */
            ->leftjoin('App:EcuSwParameterValuesSets', 'dvs', 'WITH',
                'pa.usedDefaultValue= dvs.ecuSwParameterValueSetId')
            ->leftjoin('App:EcuSwParameterValuesSets', 'cvs', 'WITH',
                'pa.usedConstantValue = cvs.ecuSwParameterValueSetId')
            /* --------------------------------------- */
            /*-----------------*/
            ->leftjoin('App:Units', 'u', 'WITH',
                'pa.unit = u.unitId')
            ->leftjoin('App:EcuCommunicationProtocols', 'cp', 'WITH',
                'pa.ecuCommunicationProtocol = cp.ecuCommunicationProtocolId')
            ->join('App:EcuSoftwareParameterNames', 'pn', 'WITH',
                'pa.ecuSoftwareParameterName = pn.ecuSoftwareParameterNameId')
            /* -------------------------- */
            /* --- Remove parameters that are only available in odx1 --- */
            ->where("COALESCE(odx.isAlsoOdx2, 'no') = 'no'  OR odx.isAlsoOdx2 = true")
            /* ---------------------------------------------------------- */
            ->andWhere('swm.ecuSwVersion = :sw_id')
            ->setParameters([
                'sw_id' => $sw->getEcuSwVersionId(), 'null' => null, 'empty' => '', 'R' => 'R', 'W' => 'W', 'C' => 'C',
            ])
            ->addOrderBy('type_order', 'ASC')
            ->addOrderBy('order', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getParametersOdx1BySwId(EcuSwVersions $sw)
    {
        return $this->createQueryBuilder('swm')
            /* --- Linked Values, Types --- */
            // Type
            ->select('CASE 
                    WHEN pa.linkedToGlobalParameter IS NOT NULL THEN gpvt.variableTypeName
                    ELSE vt.variableTypeName END as type
                ')
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN cocvt.variableTypeName
            // Value -> Name for Dynamic, Global, Coc
            ->addSelect('CASE
                    WHEN pa.linkedToGlobalParameter IS NOT NULL THEN gp.globalParameterName
                    WHEN pa.dynamicParameterValuesByDiagnosticSoftware IS NOT NULL THEN dv.dynamicParameterValuesByDiagnosticSoftwareName
                    WHEN dvs.valueString IS NOT NULL  THEN dvs.valueString
                    WHEN cvs.valueString IS NOT NULL THEN cvs.valueString
                    ELSE :null
                    END as value_string
                ')
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN coc.cocParameterName
            ->addSelect('CASE

                        WHEN dvs.valueBool IS NOT NULL  THEN dvs.valueBool
                        WHEN cvs.valueBool IS NOT NULL THEN cvs.valueBool
                        ELSE :null
                        END as value_bool
                    ')
            ->addSelect('CASE

                        WHEN dvs.valueInteger IS NOT NULL  THEN dvs.valueInteger
                        WHEN cvs.valueInteger IS NOT NULL THEN cvs.valueInteger
                        ELSE :null
                        END as value_integer
                    ')
            ->addSelect('CASE

                        WHEN dvs.valueUnsigned IS NOT NULL  THEN dvs.valueUnsigned
                        WHEN cvs.valueUnsigned IS NOT NULL THEN cvs.valueUnsigned
                        ELSE :null
                        END as value_unsigned
                    ')
            /* Linking Type */
            ->addSelect("CASE
                WHEN pa.linkedToGlobalParameter IS NOT NULL THEN 'Global Parameter'
                WHEN pa.dynamicParameterValuesByDiagnosticSoftware IS NOT NULL THEN 'Dynamic'
                WHEN pa.usedDefaultValue IS NOT NULL THEN 'Default'
                WHEN pa.usedConstantValue IS NOT NULL THEN 'Constant'
                ELSE 'Undefined'
                END as linking_type")
//            WHEN pa.linkedToCocParameter IS NOT NULL THEN 'CoC'
            /* ----------------------------------- */
            /* --- R/W/C --- */
            ->addSelect('CASE WHEN pa.shouldReadParameterValueFromEcu = true THEN :R ELSE :empty END as read')
            ->addSelect('CASE WHEN pa.shouldWriteParameterValueToEcu = true THEN :W ELSE :empty END as write')
            ->addSelect('CASE WHEN pa.shouldConfirmParameterValueFromEcu = true THEN :C ELSE :empty END as confirm')
            /* ------------- */
            /* --- Support odx2 --- */
            ->addSelect("CASE WHEN odx.isAlsoOdx2 = true THEN 'true' ELSE 'false' END as odx2")
            /* -------------------- */
            ->addSelect('odx.specialOrderIdForOdx1 as order', 'u.unitName as unit', 'pn.ecuSoftwareParameterName as name',
                'IDENTITY(pa.linkedToGlobalParameter)', 'IDENTITY(pa.dynamicParameterValuesByDiagnosticSoftware)',
                'pa.ecuSwParameterId as parameter_id', 'IDENTITY(pa.linkedToGlobalParameter) AS global_parameter_id',
                'IDENTITY(pa.dynamicParameterValuesByDiagnosticSoftware) as dynamic_value_id',
                'IDENTITY(pa.linkedToCocParameter) as coc_parameter_id', 'pn.ecuSoftwareParameterNameId as name_id')
            /* -------------------- */
            ->addSelect("CASE
                WHEN pt.parameterType = 'HW' THEN 1 
                WHEN pt.parameterType = 'SW' THEN 2
                WHEN pt.parameterType = 'Serial' THEN 3
                ELSE 4 END as type_order    
            ")
            /* -------------- HEX VALUES FOR 5th IT ---------------- */
            ->addSelect("CASE
                WHEN dvs.valueHex IS NOT NULL THEN dvs.valueHex
                WHEN cvs.valueHex IS NOT NULL THEN cvs.valueHex
                ELSE :null
                END as value_hex
            ")
            /* ------------- VARIABLE TYPE ID FOR 5th IT ----------- */
            ->addSelect('vt.variableTypeId as type_id')
            /* ----------------------------------------------------- */
            ->join('App:EcuSwParameters', 'pa', 'WITH',
                'swm.ecuSwParameter = pa.ecuSwParameterId')
            ->join('App:Odx1Parameters', 'odx', 'WITH',
                'pa.ecuSwParameterId = odx.opEcuSwParameter')
            ->join('App:EcuSwParameterTypes', 'pt', 'WITH',
                'pa.ecuSwParameterType = pt.ecuSwParameterTypeId')
            /* --- Join to parameters --- */
            ->join('App:VariableTypes', 'vt', 'WITH',
                'pa.variableType = vt.variableTypeId')
            /* --- Linking and Values --- */
            /* --- Global Parameters --- */
            ->leftjoin('App:GlobalParameters', 'gp', 'WITH',
                'pa.linkedToGlobalParameter = gp.globalParameterId')
            ->leftjoin('App:VariableTypes', 'gpvt', 'WITH',
                'gp.variableType = gpvt.variableTypeId')
            /* ------------------------- */
            /* --- COC Parameters --- */
            ->leftjoin('App:CocParameters', 'coc', 'WITH',
                'pa.linkedToCocParameter = coc.cocParameterId')
            ->leftjoin('App:VariableTypes', 'cocvt', 'WITH',
                'coc.variableType = cocvt.variableTypeId')
            /* ---------------------- */
            /* --- Dynamic Parameters --- */
            ->leftjoin('App:DynamicParameterValuesByDiagnosticSoftware', 'dv', 'WITH',
                'pa.dynamicParameterValuesByDiagnosticSoftware = dv.dpvbdsId')
            /* -------------------------- */
            /* --- Default and Constant Parameters --- */
            ->leftjoin('App:EcuSwParameterValuesSets', 'dvs', 'WITH',
                'pa.usedDefaultValue= dvs.ecuSwParameterValueSetId')
            ->leftjoin('App:EcuSwParameterValuesSets', 'cvs', 'WITH',
                'pa.usedConstantValue = cvs.ecuSwParameterValueSetId')
            /* --------------------------------------- */
            /*-----------------*/
            ->leftjoin('App:Units', 'u', 'WITH',
                'pa.unit = u.unitId')
            ->join('App:EcuSoftwareParameterNames', 'pn', 'WITH',
                'pa.ecuSoftwareParameterName = pn.ecuSoftwareParameterNameId')
            /* -------------------------- */
            ->where('swm.ecuSwVersion = :sw_id')
            ->setParameters([
                'sw_id' => $sw->getEcuSwVersionId(), 'null' => null, 'empty' => '', 'R' => 'R', 'W' => 'W', 'C' => 'C',
                ])
            ->addOrderBy('type_order', 'ASC')
            ->addOrderBy('order', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

