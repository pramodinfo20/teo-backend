<?php

namespace App\Repository;

use App\Entity\ResponsibilityEcus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResponsibilityEcus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponsibilityEcus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponsibilityEcus[]    findAll()
 * @method ResponsibilityEcus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsibilityEcusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResponsibilityEcus::class);
    }

    public function checkIfUserOrgAlreadyAssignedForEcu(int $userOrg, string $ecuId, bool $isOrganization)
    {
        $column = $isOrganization ? 'ra.stsOs' : 'ra.assignedUser';

        $qb = $this->createQueryBuilder('re');

        $result = $qb
            ->join('App:ResponsibilityAssignments', 'ra', 'WITH', 'ra.raId = re.respAssignments')
            ->where('re.ecu = :ecuId')
            ->andWhere($column . ' = :userId')
            ->setParameter('ecuId', $ecuId)
            ->setParameter('userId', $userOrg)
            ->getQuery()
            ->getScalarResult();

        return (bool)$result;
    }
}
