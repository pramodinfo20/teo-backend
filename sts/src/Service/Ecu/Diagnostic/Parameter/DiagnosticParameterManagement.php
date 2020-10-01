<?php

namespace App\Service\Ecu\Diagnostic\Parameter;

use App\Entity\DynamicParameterValuesByDiagnosticSoftware;
use App\Model\Diagnostic\DynamicParameter;
use App\Model\Diagnostic\DynamicParametersCollection;
use App\Service\AbstractService;
use Exception;

class DiagnosticParameterManagement extends AbstractService
{
    /**
     * Retrieve parameters for dynamic and set it in model
     *
     * @return DynamicParametersCollection
     */
    public function getParameters() {
        $parameters = $this->manager->getRepository(DynamicParameterValuesByDiagnosticSoftware::class)->findAll();

        $dynamicParametersCollection = new DynamicParametersCollection();

        foreach ($parameters as $parameter) {
            $dynamicParameter = new DynamicParameter();
            $dynamicParameter->setParameterId($parameter->getDpvbdsId())
                            ->setValue($parameter->getDynamicParameterValuesByDiagnosticSoftwareName());

            $dynamicParametersCollection->addParameters($dynamicParameter);
        }

        return $dynamicParametersCollection;
    }

    /**
     * Save Dynamic Parameters
     *
     * @param DynamicParametersCollection $collection
     * @param array $parametersBag
     *
     * @throws Exception
     */
    public function save(
        DynamicParametersCollection $collection,
        array $parametersBag
    ): void
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $proceededParameters = [];

        try {
            foreach ($collection->getParameters() as $parameter) {
                /* Decide to add or to edit parameters */
                if ($parameter->getParameterId()) {
                    $dynamicParameter = $this->entityManager->getRepository
                    (DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($parameter->getParameterId());
                } else {
                    $dynamicParameter = new DynamicParameterValuesByDiagnosticSoftware();
                }

                $dynamicParameter->setDynamicParameterValuesByDiagnosticSoftwareName($parameter->getValue());

                $this->entityManager->persist($dynamicParameter);
                $this->entityManager->flush();

                $proceededParameters[$parameter->getParameterId()] = $parameter;
            }

            /* Is our parameter still needed? If not present in parameters bag, remove! */
            if ($missingParameters = $this->findMissingParameters($parametersBag, $proceededParameters)) {
                foreach ($missingParameters as $missingParameter) {
                    $parameterToDelete =  $dynamicParameter = $this->entityManager->getRepository
                    (DynamicParameterValuesByDiagnosticSoftware::class)
                        ->find($missingParameter->getParameterId());
                    $this->entityManager->remove($parameterToDelete);
                }
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
}