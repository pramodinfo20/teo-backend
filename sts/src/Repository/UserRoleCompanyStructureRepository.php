<?php

namespace App\Repository;

use App\Entity\UserRoleCompanyStructure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserRoleCompanyStructure|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRoleCompanyStructure|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRoleCompanyStructure[]    findAll()
 * @method UserRoleCompanyStructure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRoleCompanyStructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRoleCompanyStructure::class);
    }

    public function getAssignedUserRoleToOrgStructure()
    {
        return $this->createQueryBuilder('u')
            ->select('IDENTITY(u.userRole) AS user_role, IDENTITY(u.stsOrganizationStructure) AS sts_org_struct')
            ->getQuery()
            ->getResult();
    }


    public function findNotEqualById(int $currentRole, array $ids)
    {
        if (!empty($ids)) {
            $query = $this->createQueryBuilder('u')->expr()->notIn('u.stsOrganizationStructure', $ids);
        } else {
            $query = '1=1';
        }

        return $this->createQueryBuilder('u')
            ->andWhere('u.userRole = :role')
            ->andWhere($query)
            ->setParameter(':role', $currentRole)
            ->getQuery()
            ->getResult();
    }
}
