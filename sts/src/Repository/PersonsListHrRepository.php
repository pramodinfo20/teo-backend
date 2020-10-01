<?php

namespace App\Repository;

use App\Entity\PersonsListHr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

/**
 * @method PersonsListHr|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonsListHr|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonsListHr[]    findAll()
 * @method PersonsListHr[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonsListHrRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, PersonsListHr::class);
  }

  public function getLastUploadId(): ?int
  {
    return $this->createQueryBuilder('p0')
        ->select('MAX(p0.upload)')
        ->getQuery()
        ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
  }

  public function getLastUploadList(): ?array
  {
    $lastUploadId = " 1=1 ";

    if (!is_null($this->getLastUploadId())) {
      $lastUploadId = $this->createQueryBuilder('p')->expr()->eq('p.upload', $this->getLastUploadId());
    }

    return $this->createQueryBuilder('p')
        ->select('p')
        ->where($lastUploadId)
        ->getQuery()
        ->getResult();
  }


  public function getOrganizationStructureWithNames()
  {
    $lastUploadId = " 1=1 ";

    if (!is_null($this->getLastUploadId())) {
      $lastUploadId = $this->createQueryBuilder('p')->expr()->eq('p.upload', $this->getLastUploadId());
    }

    return $this->createQueryBuilder('p')
        ->select('IDENTITY(p.organization) as id, s1.name as name')
        ->addSelect('IDENTITY(p.deputyOrganization) as parent_id, s2.name as parentName')
        ->addSelect('p.person')
        ->leftJoin('App:stsOrganizationStructure', 's1', 'WITH',
            'p.organization = s1.id')
        ->leftJoin('App:stsOrganizationStructure', 's2', 'WITH',
            'p.deputyOrganization = s2.id')
        ->orderBy('s1.id', 'ASC')
        ->where('p.isLeader = :t')
        ->andWhere($lastUploadId)
        ->setParameter(':t', true)
        ->getQuery()
        ->getResult();
  }
}

