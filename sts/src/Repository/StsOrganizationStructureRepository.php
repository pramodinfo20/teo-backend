<?php

namespace App\Repository;

use App\Entity\StsOrganizationStructure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StsOrganizationStructure|null find($id, $lockMode = null, $lockVersion = null)
 * @method StsOrganizationStructure|null findOneBy(array $criteria, array $orderBy = null)
 * @method StsOrganizationStructure[]    findAll()
 * @method StsOrganizationStructure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StsOrganizationStructureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StsOrganizationStructure::class);
    }

    public function getStructureList(string $name) {

        $queryBuilder = $this->createQueryBuilder('sos');

        $result = $queryBuilder
            ->select("sos.id, sos.name")
            ->where("REGEX(sos.name, :regex) = true")
            ->setParameter("regex", $name)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
