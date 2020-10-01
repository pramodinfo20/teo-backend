<?php

namespace App\Repository;

use App\Entity\EcuReleaseDeadline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuReleaseDeadline|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuReleaseDeadline|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuReleaseDeadline[]    findAll()
 * @method EcuReleaseDeadline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAdminRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuReleaseDeadline::class);
    }

    /**
     * @param int $ecuId
     * @param int $role
     * @return mixed
     */
    public function adddeadline(string $xDeadline, string $yDeadline, string $zDeadline)
    {

        $qb = $this->createQueryBuilder('erd');

        $result = $qb
            ->select('erd.xDeadline, erd.yDeadline, erd.zDeadline, u.id')
            ->Join('App:Users', 'u', 'WITH', 'erd.assigneUser = u.id')
            ->where('erd.xDeadline = :xdead')
            ->andWhere('erd.yDeadline = :ydead')
            ->andWhere('erd.zDeadline = :zdead')
            ->setParameter('xdead', $xDeadline)
            ->setParameter('ydead', $yDeadline)
            ->setParameter('zdead', $zDeadline)
            ->getQuery()
            ->getResult();

        return $result;
    }
}