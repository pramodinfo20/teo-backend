<?php

namespace App\Service\Parameter;

use App\Entity\CocParameters;
use App\Entity\CocParameterValuesSets;
use App\Entity\CocParameterValuesSetsMapping;
use App\Entity\CocParameterRelease;
use App\Entity\ReleaseStatus;
use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Enum\Entity\VariableTypes;
use App\Model\Parameter\CocParameter as CocParameterModel;
use App\Model\Parameter\CocCollection;
use App\Service\AbstractService;
use App\Service\History\Parameter\HistoryCocParameterI;
use DateTime;

class CocParameter extends AbstractService implements HistoryCocParameterI
{
    /**
     * Retrieve Coc parameters and set it in model
     *
     * @param SubVehicleConfigurations $subConfiguration
     *
     * @return CocCollection
     */
    public function getCocParameters(SubVehicleConfigurations $subConfiguration): CocCollection
    {
        $cocCollection = new CocCollection();

        $cocParameters = $this->manager
            ->getRepository(CocParameters::class)
            ->getParametersBySubConfiguration($subConfiguration);
        $allCocParameters = $this->manager->getRepository(CocParameters::class)
            ->findBy([], ['parameterOrder' => 'ASC']);
        $flag = false;
        $cocParameter = null;
        $counter = 0;
        if (is_array($allCocParameters) && count($allCocParameters)) {
            foreach ($allCocParameters as $aParameter) {
                $flag = false;
                foreach ($cocParameters as $parameter) {
                    if ($parameter['cocParameterId'] == $aParameter->getCocParameterId()) {
                        $flag = true;
                        $cocParameter = new CocParameterModel();
                        $cocParameter->setCocParameterId($parameter['cocParameterId'])
                            ->setCocParameterName($parameter['cocParameterName'])
                            ->setVariableTypeId($parameter['variableTypeId'])
                            ->setVariableTypeName($parameter['variableTypeName'])
                            ->setResponsibleUserId($parameter['responsibleUserId'])
                            ->setResponsibleUser($parameter['responsibleUserFname']
                                . " " . $parameter['responsibleUserLname'])
                            ->setDescription($parameter['description'])
                            ->setUnitName($parameter['name'])
                            ->setField($parameter['field'])
                            ->setCocParameterValueSetId($parameter['cocParameterValueSetId'])
                            ->setValueString($parameter['valueString'])
                            ->setValueDate($parameter['valueDate'])
                            ->setValueBool($parameter['valueBool'])
                            ->setValueDouble($parameter['valueDouble'])
                            ->setValueInteger($parameter['valueInteger'])
                            ->setValueBigInteger($parameter['valueBiginteger'])
                            ->setValueHex($parameter['valueHex'])
                            ->setCounter(++$counter);
                        break;
                    }
                }
                if (!$flag) {
                    $cocParameter = new CocParameterModel();
                    $cocParameter->setCocParameterId($aParameter->getCocParameterId())
                        ->setCocParameterName($aParameter->getCocParameterName())
                        ->setVariableTypeId($aParameter->getVariableType()->getVariableTypeId())
                        ->setVariableTypeName($aParameter->getVariableType()->getVariableTypeName())
                        ->setResponsibleUserId($aParameter->getResponsibleUser()->getId())
                        ->setResponsibleUser($aParameter->getResponsibleUser()->getFname()
                            . " " . $aParameter->getResponsibleUser()->getLname())
                        ->setDescription($aParameter->getDescription())
                        ->setUnitName(is_null($aParameter->getUnit()) ? null : $aParameter->getUnit()->getName())
                        ->setField($aParameter->getField())
                        ->setCocParameterValueSetId(null)
                        ->setValueString(null)
                        ->setValueDate(null)
                        ->setValueBool(null)
                        ->setValueDouble(null)
                        ->setValueInteger(null)
                        ->setValueBigInteger(null)
                        ->setValueHex(null)
                        ->setCounter(++$counter);
                }
                $cocCollection->addParameters($cocParameter);
            }
        }

        return $cocCollection;
    }

    /**
     *  Take CoC released parameters for sub-configuration
     *
     * @param SubVehicleConfigurations $subVehicleConfiguration
     *
     * @return CocParameterRelease
     * @throws \Exception
     */
    public function &getCoCReleasedParameters(SubVehicleConfigurations $subVehicleConfiguration): CocParameterRelease
    {
        $cocReleased = null;

        $cocReleasedParameter = $this->manager->getRepository(CocParameterRelease::class)
            ->find($subVehicleConfiguration->getSubVehicleConfigurationId());

        if (!is_null($cocReleasedParameter)) {
            $cocReleased = $cocReleasedParameter;
        } else {
            $cocReleased = new CocParameterRelease();
            $cocReleased->setCprSubVehicleConfiguration($subVehicleConfiguration);

            $releaseStatus = $this->entityManager->getRepository(ReleaseStatus::class)->find(1);
            $cocReleased->setReleaseStatus($releaseStatus);
        }
        $releaseDate = new DateTime(date('Y-m-d'));
        $releaseUser = $_SESSION['sts_userid'];
        $releaseBy = $this->manager->getRepository(Users::class)->findOneBy(
            ['id' => $releaseUser]
        );

        $cocReleased->setReleasedDate($releaseDate);
        $cocReleased->setReleasedBy($releaseBy);

        return $cocReleased;
    }

    /**
     * Save a non-entity data from form with transactions
     *
     * @param CocCollection $cocCollection
     * @param SubVehicleConfigurations $subVehicleConfigurations
     *
     * @throws \Exception
     */
    public function save(CocCollection $cocCollection, SubVehicleConfigurations $subVehicleConfigurations): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {

            foreach ($cocCollection->getParameters() as $valueSet) {
                $cocParameterManager = $this->manager->getRepository(CocParameters::class);
                if (!$valueSet->getCocParameterValueSetId()) {
                    $cocParameterValueSets = new CocParameterValuesSets();
                    $cocParameterValueSets->setCocParameter($cocParameterManager->find($valueSet->getCocParameterId()));
                } else {
                    $cocParameterValueSets = $this->entityManager->getRepository(CocParameterValuesSets::class)
                        ->find($valueSet->getCocParameterValueSetId());
                }

                switch ($valueSet->getVariableTypeId()) {
                    case VariableTypes::VARIABLE_TYPE_STRING:
                    case VariableTypes::VARIABLE_TYPE_ASCII:
                    case VariableTypes::VARIABLE_TYPE_BLOB:
                        $cocParameterValueSets->setValueString($valueSet->getValueString());
                        break;
                    case VariableTypes::VARIABLE_TYPE_INTEGER:
                    case VariableTypes::VARIABLE_TYPE_SIGNED:
                    case VariableTypes::VARIABLE_TYPE_UNSIGNED:
                        $cocParameterValueSets->setValueInteger($valueSet->getValueInteger());
                        break;
                    case VariableTypes::VARIABLE_TYPE_BIGINTEGER:
                        $cocParameterValueSets->setValueBiginteger($valueSet->getValueBigInteger());
                        break;
                    case VariableTypes::VARIABLE_TYPE_DATE:
                        $cocParameterValueSets->setValueDate($valueSet->getValueDate());
                        break;
                    case VariableTypes::VARIABLE_TYPE_DOUBLE:
                        $cocParameterValueSets->setValueDouble($valueSet->getValueDouble());
                        break;
                    case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                        $cocParameterValueSets->setValueBool($valueSet->isValueBool());
                        break;
                }


                $this->entityManager->persist($cocParameterValueSets);

                $cocParameterValueSetsMapping = $this->entityManager->getRepository(CocParameterValuesSetsMapping::class)
                    ->findOneBy(
                        [
                            'subVehicleConfiguration' => $subVehicleConfigurations,
                            'cocParameterValueSet' => $cocParameterValueSets
                        ]
                    );

                if (!$cocParameterValueSetsMapping) {
                    $cocParameterValueSetsMapping = new CocParameterValuesSetsMapping();
                    $cocParameterValueSetsMapping->setCocParameterValueSet($cocParameterValueSets);
                    $cocParameterValueSetsMapping->setSubVehicleConfiguration($subVehicleConfigurations);

                    $this->entityManager->persist($cocParameterValueSetsMapping);
                }
            }
            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    /**
     * Save  entity data from form with transactions
     *
     * @param CocParameterRelease $cocParameterRelease
     *
     * @throws \Exception
     */
    public function saveCoCReleased(CocParameterRelease $cocParameterRelease): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            // In case of one of approval is not put correctly, prevent save this parameters to DB.
            /*if (is_null($cocParameterRelease->getApprovalCode()) or is_null($cocParameterRelease->getApprovalDate())) {
                $cocParameterRelease->setApprovalCode(null);
                $cocParameterRelease->setApprovalDate(null);
            } else*/ 
            // ApprovalCode must be saved without a ApprovalDate
            if (!is_null($cocParameterRelease->getApprovalCode()) and !is_null($cocParameterRelease->getApprovalDate())) {
                $releaseStatus = $this->entityManager->getRepository(ReleaseStatus::class)->find(2);
                $cocParameterRelease->setReleaseStatus($releaseStatus);
            }

            $this->entityManager->persist($cocParameterRelease);
            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }
}