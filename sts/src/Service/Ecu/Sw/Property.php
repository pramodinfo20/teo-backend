<?php


namespace App\Service\Ecu\Sw;


use App\Entity\CanVersions;
use App\Entity\EcuSwProperties;
use App\Entity\EcuSwPropertiesMapping;
use App\Entity\EcuSwVersions;
use App\Model\EcuSwProperties\EcuSwPropertiesCollection;
use App\Model\EcuSwProperties\EcuSwPropertiesModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class Property
{

    private $objectManager;

    private $entityManager;

    /**
     * Property constructor.
     * @param ObjectManager $objectManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $objectManager, EntityManagerInterface $entityManager)
    {
        $this->objectManager = $objectManager;
        $this->entityManager = $entityManager;
    }

    public function getProperties(int $swVersion): EcuSwPropertiesCollection
    {
        $propertiesCollection = new EcuSwPropertiesCollection();

        $ecuSwPropertiesList = $this->objectManager->getRepository(EcuSwProperties::class)->getPropertiesForEcuSwVersion($swVersion);

        if (is_array($ecuSwPropertiesList) && count($ecuSwPropertiesList)) {
            foreach ($ecuSwPropertiesList as $ecuSwProperty) {
                $ecuSwPropertyObject = new EcuSwPropertiesModel();
                $ecuSwPropertyObject->setId($ecuSwProperty['ecuSwPropertyId']);
                $ecuSwPropertyObject->setName($ecuSwProperty['name']);
                $ecuSwPropertyObject->setValue($ecuSwProperty['value']);
                $ecuSwPropertyObject->setOrder($ecuSwProperty['propertyOrder']);

                $propertiesCollection->addProperties($ecuSwPropertyObject);
            }
        }
        return $propertiesCollection;
    }

    public function getPropForAddFromList(int $swVersion): EcuSwPropertiesCollection
    {
        $propertiesCollection = new EcuSwPropertiesCollection();

        $ecuSwPropertiesList = $this->objectManager->getRepository(EcuSwProperties::class)->getAllPropertiesBySw($swVersion);
//        $ecuSwPropertiesList = $this->objectManager->getRepository(EcuSwProperties::class)->findAll();

        if (is_array($ecuSwPropertiesList) && count($ecuSwPropertiesList)) {
            foreach ($ecuSwPropertiesList as $ecuSwProperty) {
                $ecuSwPropertyObject = new EcuSwPropertiesModel();
                $ecuSwPropertyObject->setId($ecuSwProperty['ecuSwPropertyId']);
                $ecuSwPropertyObject->setName($ecuSwProperty['name']);
                $ecuSwPropertyObject->setValue($ecuSwProperty['value']);
                if (!is_null($ecuSwProperty['ecuSwVersion']))
                    $ecuSwPropertyObject->setIsAssigned(true);

                $propertiesCollection->addProperties($ecuSwPropertyObject);
            }
        }

        return $propertiesCollection;
    }

    public function changeOrders(EcuSwPropertiesCollection $ecuSwPropertiesCollection, EcuSwVersions $ecuSwVersions): void
    {
        $ecuSwPropMapEntMgr = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);

        foreach ($ecuSwPropertiesCollection->getProperties() as $id => $collectionElement) {
            $ecuSwPropMapEnt = $ecuSwPropMapEntMgr->findOneBy([
                'ecuSwProperty' => $collectionElement->getId(),
                'ecuSwVersion' => $ecuSwVersions
            ]);
            $ecuSwPropMapEnt->setPropertyOrder($id + 1);

            $this->entityManager->persist($ecuSwPropMapEnt);
        }
        $this->entityManager->flush();
    }

    public function addNewProperty(EcuSwPropertiesCollection $ecuSwPropertiesCollection, EcuSwVersions $ecuSwVersions): void
    {
        $ecuPropLastOrder = $this->entityManager
            ->getRepository(EcuSwPropertiesMapping::class)
            ->getLastOrderForSelectedEcuSw($ecuSwVersions->getEcuSwVersionId());

        foreach ($ecuSwPropertiesCollection->getProperties() as $collectionElement) {
            if (!$collectionElement->getId()) {
                $ecuSwPropertiesObject = new EcuSwProperties();
                $ecuSwPropertiesObject->setName($collectionElement->getName());
                $ecuSwPropertiesObject->setValue($collectionElement->getValue());

                $this->entityManager->persist($ecuSwPropertiesObject);

                $ecuSwPropertiesMappingObject = new EcuSwPropertiesMapping();
                $ecuSwPropertiesMappingObject->setEcuSwVersion($ecuSwVersions);
                $ecuSwPropertiesMappingObject->setEcuSwProperty($ecuSwPropertiesObject);
                $ecuSwPropertiesMappingObject->setPropertyOrder(++$ecuPropLastOrder);

                $this->entityManager->persist($ecuSwPropertiesMappingObject);
            }
        }
        $this->entityManager->flush();
    }

    public function addPropertyFromList(EcuSwPropertiesCollection $ecuSwPropertiesCollection, EcuSwVersions $ecuSwVersions)
    {
        $ecuSwPropMapEntMgr = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);
        $ecuSwPropEntMgr = $this->entityManager->getRepository(EcuSwProperties::class);

        $ecuSwPropMapCurrentSw = $ecuSwPropMapEntMgr->getEcuSwPropMappingEcuSwList($ecuSwVersions->getEcuSwVersionId());
        $ecuSwPropMapCurrentSw = array_column($ecuSwPropMapCurrentSw, 'ecuSwProperty');

        $ecuPropLastOrder = $ecuSwPropMapEntMgr->getLastOrderForSelectedEcuSw($ecuSwVersions->getEcuSwVersionId());

        foreach ($ecuSwPropertiesCollection->getProperties() as $collectionProperty) {
            $colId = $collectionProperty->getId();
            if (!in_array($colId, $ecuSwPropMapCurrentSw) and $collectionProperty->isAssigned()) {
                $ecuSwPropertiesMappingObject = new EcuSwPropertiesMapping();
                $ecuSwPropertiesMappingObject->setEcuSwVersion($ecuSwVersions);
                $ecuSwPropertiesMappingObject->setEcuSwProperty($ecuSwPropEntMgr->find($collectionProperty->getId()));
                $ecuSwPropertiesMappingObject->setPropertyOrder(++$ecuPropLastOrder);

                $this->entityManager->persist($ecuSwPropertiesMappingObject);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param EcuSwPropertiesCollection $ecuSwPropertiesCollection
     * @param EcuSwVersions $ecuSwVersions
     * @return array
     */
    public function checkConflictAddFromList(EcuSwPropertiesCollection $ecuSwPropertiesCollection, EcuSwVersions $ecuSwVersions)
    {
        $conflicts = [];
        $propertiesAlreadyAdded = [];

        $ecuSwPropMapEntMgr = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);
        $ecuSwPropEntMgr = $this->entityManager->getRepository(EcuSwProperties::class);

        // Properties mapped for current SW in DB.
        $ecuSwPropMapCurrentSw = $ecuSwPropMapEntMgr->getEcuSwPropMappingEcuSwList($ecuSwVersions->getEcuSwVersionId());
        $ecuSwPropMapCurrentSwIds = array_column($ecuSwPropMapCurrentSw, 'ecuSwProperty');
        $ecuSwPropMapCurrentSwName = array_column($ecuSwPropMapCurrentSw, 'name');
        $lastOrderOfProperty = $ecuSwPropMapEntMgr->getLastOrderForSelectedEcuSw($ecuSwVersions->getEcuSwVersionId());

        // Get all properties available in DB.
        $allEcuSwPropertiesInDb = $ecuSwPropEntMgr->findAll();

        // Properties to be added
        $collectionPropertiesToAdd = $ecuSwPropertiesCollection->getProperties();

        foreach ($collectionPropertiesToAdd as $collectionProperty) {
            $colPropId = $collectionProperty->getId();

            if (!in_array($colPropId, $ecuSwPropMapCurrentSwIds) and $collectionProperty->isAssigned() and
                !in_array($collectionProperty->getName(), $propertiesAlreadyAdded)) {

                $neededObject = array_filter($allEcuSwPropertiesInDb, function ($e) use ($colPropId) {
                    return $e->getEcuSwPropertyId() == $colPropId;
                });
                $neededObject = array_shift($neededObject);
                $objectName = $neededObject->getName();


                if (in_array($neededObject->getName(), $ecuSwPropMapCurrentSwName)) {

                    $propertyToOverwrite = array_filter($ecuSwPropMapCurrentSw, function ($param) use ($objectName) {
                        return $param['name'] == $objectName;
                    });
                    $propertyToOverwrite = array_shift($propertyToOverwrite);

                    array_push($conflicts, [
                        'ecu_property_name' => $collectionProperty->getName(),
                        'ecu_property_id_current' => $propertyToOverwrite['ecuSwProperty'],
                        'ecu_property_value_current' => $propertyToOverwrite['value'],
                        'ecu_property_id_destination' => $neededObject->getEcuSwPropertyId(),
                        'ecu_property_value_destination' => $neededObject->getValue(),
                    ]);


                } else {
                    $ecuSwPropertiesMappingObject = new EcuSwPropertiesMapping();
                    $ecuSwPropertiesMappingObject->setEcuSwVersion($ecuSwVersions);
                    $ecuSwPropertiesMappingObject->setEcuSwProperty($ecuSwPropEntMgr->find($collectionProperty->getId()));
                    $ecuSwPropertiesMappingObject->setPropertyOrder(++$lastOrderOfProperty);

                    $this->entityManager->persist($ecuSwPropertiesMappingObject);
                }
                array_push($propertiesAlreadyAdded, $collectionProperty->getName());
            }
        }

        $this->entityManager->flush();

        return !(empty($conflicts)) ? $conflicts : ['empty'];
    }

    /**
     * Check conflicts for selected properties
     *
     * @param int $swCurrent
     * @param int $swDestination
     * @param array $properties
     *
     * @return array
     */
    public function checkConflicts(int $swCurrent, int $swDestination, array $properties): array
    {
        $conflicts = [];
        $set = [];

        $entityManagerProperty = $this->entityManager->getRepository(EcuSwProperties::class);
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);

        $currentProperties = $entityManagerProperty->findBy(['ecuSwPropertyId' => $properties]);
        $destinationPropertiesMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $swDestination]);
        $destinationProperties = array_map(function ($element) {
            return $element->getEcuSwProperty();
        }, $destinationPropertiesMapping);


        foreach ($currentProperties as $currentKey => $currentProperty) {
            foreach ($destinationProperties as $destinationKey => $destinationProperty) {
                /* Check name */
                if ($currentProperty->getName() == $destinationProperty->getName()
                    && !(isset($set[$destinationProperty->getEcuSwPropertyId()]))) {
                    array_push($conflicts, [
                        'ecu_property_id_destination' => $destinationProperty->getEcuSwPropertyId(),
                        'ecu_property_name_destination' => $destinationProperty->getName(),
                        'ecu_property_id_current' => $currentProperty->getEcuswPropertyId(),
                        'ecu_property_name_current' => $currentProperty->getName()
                    ]);
                    $set[$destinationProperty->getEcuSwPropertyId()] = 1;
                    continue;
                }
            }
        }

        return !(empty($conflicts)) ? $conflicts : ['empty'];
    }

    /**
     * Resolve conflicts for selected properties and sw
     *
     * @param int $swDestination
     * @param array $propertiesConflict
     * @param array $propertiesWConflict
     *
     * @return string
     */
    public
    function resolveConflicts(int $swDestination, array $propertiesConflict, array $propertiesWConflict): string
    {
        $entityManagerSW = $this->entityManager->getRepository(EcuSwVersions::class);
        $entityManagerProperty = $this->entityManager->getRepository(EcuSwProperties::class);
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);

        $destinationSw = $entityManagerSW->findOneBy(['ecuSwVersionId' => $swDestination]);

        $suffix = ($destinationSw->getSuffixIfIsSubEcuSwVersion()) ? ' ---> ' . $destinationSw->getSuffixIfIsSubEcuSwVersion() : '';
        $swName = $destinationSw->getSwVersion() . $suffix;

        if (!(empty($propertiesWConflict))) {
            $this->copyWithoutConflict($swDestination, $propertiesWConflict);
        }

        foreach ($propertiesConflict as $propertyMap) {
            $currentProperty = $entityManagerProperty->find($propertyMap['current']);
            $destinationMapping = $entityManagerMapping->findOneBy(['ecuSwVersion' => $swDestination,
                'ecuSwProperty' => $propertyMap['destination']]);
            $destinationMapping->setEcuSwProperty($currentProperty);

            $this->entityManager->persist($destinationMapping);
        }

        $this->entityManager->flush();

        return $swName;
    }

    /**
     * Copy property to other sw without conflict
     *
     * @param       $swDestination
     * @param array $propertiesWConflict
     *
     * @return string
     */
    public
    function copyWithoutConflict($swDestination, array $propertiesWConflict): string
    {
        $entityManagerSW = $this->entityManager->getRepository(EcuSwVersions::class);
        $entityManagerProperty = $this->entityManager->getRepository(EcuSwProperties::class);

        $destinationSw = $entityManagerSW->findOneBy(['ecuSwVersionId' => $swDestination]);

        $suffix = ($destinationSw->getSuffixIfIsSubEcuSwVersion()) ? ' ---> ' . $destinationSw->getSuffixIfIsSubEcuSwVersion() : '';
        $swName = $destinationSw->getSwVersion() . $suffix;

        $order = $this->getMaxOrderForSwId($swDestination);

        $iterator = 0;
        foreach ($propertiesWConflict as $propertyId) {
            ++$iterator;
            $currentProperty = $entityManagerProperty->find($propertyId);

            $propertySwMapping = new EcuSwPropertiesMapping();
            $propertySwMapping->setEcuSwProperty($currentProperty);
            $propertySwMapping->setEcuSwVersion($destinationSw);
            $propertySwMapping->setPropertyOrder($order + $iterator);

            $this->entityManager->persist($propertySwMapping);
        }

        $this->entityManager->flush();

        return $swName;
    }

    /**
     * Get max order for sw
     *
     * @param int $sw
     *
     * @return int
     */
    private
    function getMaxOrderForSwId(int $sw): int
    {
        $entityManagerMapping = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);

        $propertiesMapping = $entityManagerMapping->findBy(['ecuSwVersion' => $sw], ['propertyOrder' => 'DESC'], 1);

        $order = 0;
        if (!empty($propertiesMapping)) {
            $order = reset($propertiesMapping)->getPropertyOrder();
        }

        return $order;
    }

    /**
     * Save CAN version to selected ECU Software version.
     *
     * @param EcuSwVersions $sw
     * @param int $selectedCanVersion
     */
    public
    function saveCanVersion(EcuSwVersions $sw, int $selectedCanVersion)
    {
        $canVersionObject = $this->entityManager->getRepository(CanVersions::class)->find($selectedCanVersion);

        $sw->setCanVersion($canVersionObject);

        $this->entityManager->persist($sw);
        $this->entityManager->flush();
    }

    /**
     * @param array $conflictsToResolve
     * @param EcuSwVersions $swVersions
     */
    public function addPropFromListConfResolve(array $conflictsToResolve, EcuSwVersions $swVersions)
    {
        $ecuSwPropMapEntMgr = $this->entityManager->getRepository(EcuSwPropertiesMapping::class);
        $ecuSwPropEntMgr = $this->entityManager->getRepository(EcuSwProperties::class);

        foreach ($conflictsToResolve as $conflict) {
            $curEcuSwProperty = $ecuSwPropEntMgr->find($conflict['current']);
            $destEcuSwProperty = $ecuSwPropEntMgr->find($conflict['destination']);

            $ecuSwPropMapToReplaceEnt = $ecuSwPropMapEntMgr->findOneBy([
                'ecuSwVersion' => $swVersions,
                'ecuSwProperty' => $curEcuSwProperty]);

            $ecuSwPropMapToReplaceEnt->setEcuSwProperty($destEcuSwProperty);

            $this->entityManager->persist($ecuSwPropMapToReplaceEnt);
        }

        $this->entityManager->flush();
    }
}