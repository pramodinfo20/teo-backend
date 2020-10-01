<?php

namespace App\Service\Parameter
{

    use App\Entity\GlobalParameters;
    use App\Entity\GlobalParameterValuesSets;
    use App\Entity\GlobalParameterValuesSetsMapping;
    use App\Entity\SubVehicleConfigurations;
    use App\Enum\Entity\VariableTypes;
    use App\Enum\Entity\VariableTypes as VariableTypesEnum;
    use App\Model\Parameter\GlobalParameter as GlobalParameterModel;
    use App\Service\AbstractService;
    use App\Service\Parameter\GlobalParameter\GlobalDto;
    use DateTime;
    use DateTimeZone;
    use Exception;

    class GlobalParameter extends AbstractService
    {
        /**
         * Retrieve value set for subconf and global
         *
         * @param SubVehicleConfigurations $subConf
         * @param GlobalParameters         $global
         *
         * @return GlobalParameterModel
         * @throws Exception
         */
        public function getValues(SubVehicleConfigurations $subConf, GlobalParameters $global): GlobalParameterModel
        {
            $mappings = $this->manager
                ->getRepository(GlobalParameterValuesSetsMapping::class)
                ->findBy(['subVehicleConfiguration' => $subConf]);

            $value = array_filter($mappings, function ($element) use ($global)
            {
                return $element->getGlobalParameterValuesSet()->getGlobalParameter() == $global;
            });

            $valueSet = new GlobalParameterModel();
            if (empty($value)) {
                $valueSet->setGlobalParameterId($global->getGlobalParameterId())
                    ->setMin($global->getMinValue())
                    ->setMax($global->getMaxValue())
                    ->setVariableTypeId($global->getVariableType()->getVariableTypeId())
                    ->setValueBool(null)
                    ->setValueInteger(null)
                    ->setValueString(null)
                    ->setValueUnsigned(null)
                    ->setValueHex(null)
                    ->setValueBiginteger(null)
                    ->setValueSigned(null)
                    ->setValueDouble(null)
                    ->setValueDate(null)
                    ->setGlobalParameterValueSetId(null);
            } else {
                $valueSet->setGlobalParameterId($global->getGlobalParameterId())
                    ->setMin($global->getMinValue())
                    ->setMax($global->getMaxValue())
                    ->setVariableTypeId($global->getVariableType()->getVariableTypeId())
                    ->setValueBool(reset($value)->getGlobalParameterValuesSet()->getValueBool())
                    ->setValueInteger(reset($value)->getGlobalParameterValuesSet()->getValueInteger())
                    ->setValueString(reset($value)->getGlobalParameterValuesSet()->getValueString())
                    ->setValueUnsigned(reset($value)->getGlobalParameterValuesSet()->getValueUnsigned())
                    ->setValueHex(reset($value)->getGlobalParameterValuesSet()->getValueHex())
                    ->setValueBiginteger(reset($value)->getGlobalParameterValuesSet()->getValueInteger())
                    ->setValueSigned(reset($value)->getGlobalParameterValuesSet()->getValueInteger())
                    ->setValueDouble(reset($value)->getGlobalParameterValuesSet()->getValueDouble())
                    ->setValueDate(reset($value)->getGlobalParameterValuesSet()->getValueDate())
                    ->setGlobalParameterValueSetId(reset($value)->getGlobalParameterValuesSet()->getGlobalParameterValuesSetId());
            }


            return $valueSet;
        }

        /**
         * Get list of globals for subconfiguration
         *
         * @param SubVehicleConfigurations $subConf
         *
         * @return array
         */
        public function getGlobalsForSubConf(SubVehicleConfigurations $subConf): array
        {
            $allGlobals = $this->manager->getRepository(GlobalParameters::class)->findAll();
            $setsMapping = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class)
                ->findBy(['subVehicleConfiguration' => $subConf]);

            $globals = [];

            foreach ($allGlobals as $global) {
                $globalDto = new GlobalDto($global->getGlobalParameterId(), $global->getGlobalParameterName(), false,
                    null);
                foreach ($setsMapping as $mapping) {
                    $valueSet = $mapping->getGlobalParameterValuesSet();
                    if ($global->getGlobalParameterId() == $valueSet->getGlobalParameter()
                            ->getGlobalParameterId()) {
                        $globalDto->setIsLinked(true);
                        if (!is_null($valueSet->getValueString())) {
                            $globalDto->setValue((string)$valueSet->getValueString());
                        } elseif (!is_null($valueSet->getValueBool())) {
                            $globalDto->setValue((string)$valueSet->getValueBool());
                        } elseif (!is_null($valueSet->getValueInteger())) {
                            $globalDto->setValue((string)$valueSet->getValueInteger());
                        } elseif (!is_null($valueSet->getValueUnsigned())) {
                            $globalDto->setValue((string)$valueSet->getValueUnsigned());
                        } elseif (!is_null($valueSet->getValueDouble())) {
                            $globalDto->setValue((string)$valueSet->getValueDouble());
                        } elseif (!is_null($valueSet->getValueDate())) {
                            $globalDto->setValue((string)$valueSet->getValueDate()->format('Y-m-d'));
                        }
                    }
                }
                array_push($globals, $globalDto);
            }

            return $globals;
        }


        /**
         * Save a non-entity data from form with transactions
         *
         * @param GlobalParameterModel     $valueSet
         * @param SubVehicleConfigurations $subVehicleConfigurations
         *
         * @throws Exception
         */
        public function save(GlobalParameterModel $valueSet, SubVehicleConfigurations $subVehicleConfigurations): void
        {
            /* Start transaction */
            $this->entityManager->beginTransaction();

            try {
                $globalParameterManager = $this->manager->getRepository(GlobalParameters::class);
                if (!$valueSet->getGlobalParameterValueSetId()) {
                    $globalParameterValueSets = new GlobalParameterValuesSets();
                    $globalParameterValueSets->setGlobalParameter($globalParameterManager->find($valueSet->getGlobalParameterId()));
                } else {
                    $globalParameterValueSets = $this->entityManager->getRepository(GlobalParameterValuesSets::class)
                        ->find($valueSet->getGlobalParameterValueSetId());
                }

                switch ($valueSet->getVariableTypeId()) {
                    case VariableTypes::VARIABLE_TYPE_STRING:
                    case VariableTypes::VARIABLE_TYPE_ASCII:
                    case VariableTypes::VARIABLE_TYPE_BLOB:
                        $globalParameterValueSets->setValueString($valueSet->getValueString());
                        break;
                    case VariableTypes::VARIABLE_TYPE_DATE:
                        $globalParameterValueSets->setValueDate($valueSet->getValueDate());
                        break;
                    case VariableTypes::VARIABLE_TYPE_INTEGER:
                        $globalParameterValueSets->setValueInteger($valueSet->getValueInteger());
                        break;
                    case VariableTypes::VARIABLE_TYPE_SIGNED:
                        $globalParameterValueSets->setValueInteger($valueSet->getValueSigned());
                        break;
                    case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                        $globalParameterValueSets->setValueInteger($valueSet->getValueBiginteger());
                        break;
                    case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                        $globalParameterValueSets->setValueUnsigned($valueSet->getValueUnsigned());
                        break;
                    case VariableTypes::VARIABLE_TYPE_DOUBLE:
                        $globalParameterValueSets->setValueDouble($valueSet->getValueDouble());
                        break;
                    case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                        $globalParameterValueSets->setValueBool($valueSet->isValueBool());
                        break;
                }

                $this->entityManager->persist($globalParameterValueSets);

                $globalParameterValueSetsMapping = $this->entityManager->getRepository(GlobalParameterValuesSetsMapping::class)
                    ->findOneBy(
                        [
                            'subVehicleConfiguration' => $subVehicleConfigurations,
                            'globalParameterValuesSet' => $globalParameterValueSets
                        ]
                    );
                if (!$globalParameterValueSetsMapping) {
                    $globalParameterValueSetsMapping = new GlobalParameterValuesSetsMapping();
                    $globalParameterValueSetsMapping->setGlobalParameterValuesSet($globalParameterValueSets);
                    $globalParameterValueSetsMapping->setSubVehicleConfiguration($subVehicleConfigurations);

                    $this->entityManager->persist($globalParameterValueSetsMapping);
                }

                $this->entityManager->flush();

                /* Commit */
                $this->entityManager->commit();
            } catch (Exception $exception) {
                /* Rollback */
                $this->entityManager->rollBack();
                throw $exception;
            }
        }

        /**
         * Copy values to other subconfigurations
         *
         * @param SubVehicleConfigurations $sourceConfiguration
         * @param GlobalParameters         $sourceGlobal
         * @param array                    $destination
         *
         * @return bool
         */
        public function copyValue(SubVehicleConfigurations $sourceConfiguration, GlobalParameters $sourceGlobal, array $destination): bool
        {
            $globalParametersValuesSets = $this->manager->getRepository(GlobalParameterValuesSets::class)->findBy(['globalParameter' => $sourceGlobal]);

            $subConfigurationManager = $this->manager->getRepository(SubVehicleConfigurations::class);

            $sourceValueSet = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class)->findOneBy([
                'subVehicleConfiguration' => $sourceConfiguration->getSubVehicleConfigurationId(),
                'globalParameterValuesSet' => $globalParametersValuesSets
            ])->getGlobalParameterValuesSet();


            foreach ($destination as $destinationConfiguration) {

                $valueSetExist = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class)->findOneBy([
                    'subVehicleConfiguration' => $subConfigurationManager->find($destinationConfiguration),
                    'globalParameterValuesSet' => $globalParametersValuesSets
                ]);

                if (empty($valueSetExist)) {
                    $newValueSet = clone $sourceValueSet;
                    $this->entityManager->persist($newValueSet);
                    $newValueSetMapping = new GlobalParameterValuesSetsMapping();
                    $newValueSetMapping->setSubVehicleConfiguration($subConfigurationManager->find($destinationConfiguration));
                    $newValueSetMapping->setGlobalParameterValuesSet($newValueSet);
                    $this->entityManager->persist($newValueSetMapping);
                } else {
                    $changeValueSet = $this->manager->getRepository(GlobalParameterValuesSets::class)->find($valueSetExist->getGlobalParameterValuesSet());

                    $changeValueSet->setValueString(null);
                    $changeValueSet->setValueBool(null);
                    $changeValueSet->setValueDouble(null);
                    $changeValueSet->setValueInteger(null);
                    $changeValueSet->setValueUnsigned(null);
                    $changeValueSet->setValueHex(null);
                    $changeValueSet->setValueDate(null);

                    switch (VariableTypesEnum::getVariableTypeByName($sourceGlobal->getVariableType())) {
                        case VariableTypesEnum::VARIABLE_TYPE_ASCII:
                            $changeValueSet->setValueString($sourceValueSet->getValueString());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_STRING:
                            $changeValueSet->setValueString($sourceValueSet->getValueString());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_BLOB:
                            $changeValueSet->setValueString($sourceValueSet->getValueString());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_INTEGER:
                            $changeValueSet->setValueInteger($sourceValueSet->getValueInteger());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_UNSIGNED:
                            $changeValueSet->setValueUnsigned($sourceValueSet->getValueUnsigned());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_DOUBLE:
                            $changeValueSet->setValueDouble($sourceValueSet->getValueDouble());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_BOOLEAN:
                            $changeValueSet->setValueBool($sourceValueSet->getValueBool());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_BIGINTEGER:
                            $changeValueSet->setValueInteger($sourceValueSet->getValueInteger());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_DATE:
                            $changeValueSet->setValueDate($sourceValueSet->getValueDate());
                            break;
                        case VariableTypesEnum::VARIABLE_TYPE_SIGNED:
                            $changeValueSet->setValueInteger($sourceValueSet->getValueInteger());
                            break;
                    }
                    $this->entityManager->persist($changeValueSet);
                }
            }

            $this->entityManager->flush();

            return true;
        }

    }
}

/* --------------------------------- NESTED CLASS ---------------------------------------------- */
namespace App\Service\Parameter\GlobalParameter
{
    class GlobalDto
    {
        /**
         * @var int
         */
        public $globalParameterId;
        /**
         * @var string
         */
        public $globalParameterName;
        /**
         * @var bool
         */
        public $isLinked;
        /**
         * @var string
         */
        public $value;


        /**
         * Parameter constructor.
         *
         * @param int           $globalParameterId
         * @param string        $globalParameterName
         * @param bool          $isLinked
         * @param string   $value
         */
        public function __construct(int $globalParameterId, string $globalParameterName, bool $isLinked = null, string
        $value = null)
        {
            $this->globalParameterId = $globalParameterId;
            $this->globalParameterName = $globalParameterName;
            $this->isLinked = $isLinked;
            $this->value = $value;
        }

        /**
         * @param bool $isLinked
         */
        public function setIsLinked(bool $isLinked = null): void
        {
            $this->isLinked = $isLinked;
        }

        /**
         * @param string $value
         */
        public function setValue(string $value = null): void
        {
            $this->value = $value;
        }
    }
}
/* ------------------------------------------------------------------------------------------ */