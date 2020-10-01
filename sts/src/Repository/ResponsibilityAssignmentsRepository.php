<?php

namespace App\Repository;

use App\Entity\ResponsibilityAssignments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResponsibilityAssignments|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponsibilityAssignments|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponsibilityAssignments[]    findAll()
 * @method ResponsibilityAssignments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsibilityAssignmentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResponsibilityAssignments::class);
    }

    /**
     * @param int $ecuId
     * @param int $role
     * @return mixed
     */
    public function getResponsiblePersonsForEcu(int $ecuId, int $role)
    {
        $roleArray = array(
            ['isResponsible' => false, 'isDeputy' => false],
            ['isResponsible' => false, 'isDeputy' => true],
            ['isResponsible' => true, 'isDeputy' => false]);

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('u.id as userId, sor.id as osId, ra.isStructure, ra.isResponsible, ra.isDeputy, ra.structureDetails, ra.responsibilityRole')
            ->addSelect("
                CASE
                WHEN ra.assignedUser IS NOT NULL THEN CONCAT(u.fname, ' ', u.lname, ' (', u.email, ')')
                ELSE sor.name
                END AS name")
            ->leftJoin('App:Users', 'u', 'WITH', 'ra.assignedUser = u.id')
            ->leftJoin('App:StsOrganizationStructure', 'sor', 'WITH', 'ra.stsOs = sor.id')
            ->join('App:ResponsibilityEcus', 're', 'WITH', 'ra.raId = re.respAssignments')
            ->where('re.ecu = :ecuId')
            ->andWhere('ra.isResponsible = :responsible')
            ->andWhere('ra.isDeputy = :deputy')
            ->setParameter('ecuId', $ecuId)
            ->setParameter('responsible', $roleArray[$role]['isResponsible'])
            ->setParameter('deputy', $roleArray[$role]['isDeputy'])
            ->orderBy('name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param string $modelRangeCharacter
     * @param int $role
     * @return mixed
     */
    public function getResponsiblePersonsForModelRange(string $modelRangeCharacter, int $role)
    {
        $roleArray = array(
            ['isResponsible' => false, 'isDeputy' => false],
            ['isResponsible' => false, 'isDeputy' => true],
            ['isResponsible' => true, 'isDeputy' => false]);

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('u.id as userId, sor.id as osId, ra.isStructure, ra.isResponsible, ra.isDeputy, ra.structureDetails, ra.responsibilityRole')
            ->addSelect("
                CASE
                WHEN ra.assignedUser IS NOT NULL THEN CONCAT(u.fname, ' ', u.lname, ' (', u.email, ')')
                ELSE sor.name
                END AS name")
            ->leftJoin('App:Users', 'u', 'WITH', 'ra.assignedUser = u.id')
            ->leftJoin('App:StsOrganizationStructure', 'sor', 'WITH', 'ra.stsOs = sor.id')
            ->join('App:ResponsibilityModelRange', 'rmr', 'WITH', 'ra.raId = rmr.respAssignments')
            ->where('rmr.name = :rmrName')
            ->andWhere('ra.isResponsible = :responsible')
            ->andWhere('ra.isDeputy = :deputy')
            ->setParameter('rmrName', $modelRangeCharacter)
            ->setParameter('responsible', $roleArray[$role]['isResponsible'])
            ->setParameter('deputy', $roleArray[$role]['isDeputy'])
            ->orderBy('name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int $category
     * @param int $role
     * @return mixed
     */
    public function getResponsiblePersons(int $category, int $role)
    {
        $roleArray = array(
            ['isResponsible' => false, 'isDeputy' => false],
            ['isResponsible' => false, 'isDeputy' => true],
            ['isResponsible' => true, 'isDeputy' => false]);

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('u.id as userId, sor.id as osId, ra.isStructure, ra.isResponsible, ra.isDeputy, ra.structureDetails, ra.responsibilityRole')
            ->addSelect("
                CASE
                WHEN ra.assignedUser IS NOT NULL THEN CONCAT(u.fname, ' ', u.lname, ' (', u.email, ')')
                ELSE sor.name
                END AS name")
            ->leftJoin('App:Users', 'u', 'WITH', 'ra.assignedUser = u.id')
            ->leftJoin('App:StsOrganizationStructure', 'sor', 'WITH', 'ra.stsOs = sor.id')
            ->where('ra.assignedCategory = :category')
            ->andWhere('ra.isResponsible = :responsible')
            ->andWhere('ra.isDeputy = :deputy')
            ->setParameter('category', $category)
            ->setParameter('responsible', $roleArray[$role]['isResponsible'])
            ->setParameter('deputy', $roleArray[$role]['isDeputy'])
            ->orderBy('name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int $ecuId
     * @param int $id
     * @param bool $isStructure
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getRespAssignIdForSelectedEcu(int $ecuId, int $id, bool $isStructure = false)
    {
        $column = 'ra.assignedUser';
        if ($isStructure)
            $column = 'ra.stsOs';

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityEcus', 're', 'WITH', 'ra.raId = re.respAssignments')
            ->where('re.ecu = :ecuId')
            ->andWhere($column . ' = :id')
            ->setParameter('ecuId', $ecuId)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param string $modelRange
     * @param int $id
     * @param bool $isStructure
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getRespAssignIdForSelectedModelRange(string $modelRange, int $id, bool $isStructure = false)
    {
        $column = 'ra.assignedUser';
        if ($isStructure)
            $column = 'ra.stsOs';

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityModelRange', 'rmr', 'WITH', 'ra.raId = rmr.respAssignments')
            ->where('rmr.name = :modelRange')
            ->andWhere($column . ' = :id')
            ->setParameter('modelRange', $modelRange)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param int $id
     * @param bool $isStructure
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getRespAssignIdForSelected(int $id, bool $isStructure = false)
    {
        $column = 'ra.assignedUser';
        if ($isStructure)
            $column = 'ra.stsOs';

        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->andWhere($column . ' = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param string $ecuId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForResponsibleAssignedForEcu(string $ecuId)
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityEcus', 're', 'WITH', 'ra.raId = re.respAssignments')
            ->where('ra.isResponsible = true')
            ->andWhere('ra.isDeputy = false')
            ->andWhere('re.ecu = :ecuId')
            ->setParameter('ecuId', $ecuId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForResponsibleAssigned()
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->where('ra.isResponsible = true')
            ->andWhere('ra.isDeputy = false')
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param string $modelRange
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForResponsibleAssignedForModelRange(string $modelRange)
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityModelRange', 'rmr', 'WITH', 'ra.raId = rmr.respAssignments')
            ->where('ra.isResponsible = true')
            ->andWhere('ra.isDeputy = false')
            ->andWhere('rmr.name = :modelRange')
            ->setParameter('modelRange', $modelRange)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param string $ecuId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForDeputyAssignedForEcu(string $ecuId)
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityEcus', 're', 'WITH', 'ra.raId = re.respAssignments')
            ->where('ra.isResponsible = false')
            ->andWhere('ra.isDeputy = true')
            ->andWhere('re.ecu = :ecuId')
            ->setParameter('ecuId', $ecuId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @param string $modelRange
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForDeputyAssignedForModelRange(string $modelRange)
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->join('App:ResponsibilityModelRange', 'rmr', 'WITH', 'ra.raId = rmr.respAssignments')
            ->where('ra.isResponsible = false')
            ->andWhere('ra.isDeputy = true')
            ->andWhere('rmr.name = :modelRange')
            ->setParameter('modelRange', $modelRange)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function checkIdForDeputyAssigned()
    {
        $qb = $this->createQueryBuilder('ra');

        $result = $qb
            ->select('ra.raId')
            ->where('ra.isResponsible = false')
            ->andWhere('ra.isDeputy = true')
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return $result;
    }
}
