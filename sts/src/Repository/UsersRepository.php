<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Symfony\Bridge\Doctrine\RegistryInterface;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function getUsersList(string $name) {

        $queryBuilder = $this->createQueryBuilder('u');

        $result = $queryBuilder
            ->select("u.id, CONCAT(u.fname, ' ', u.lname, ' (', u.email, ')') AS name")
            ->where("REGEX(CONCAT(u.fname, ' ', u.lname, ' (', u.email, ')'), :regex) = true")
            ->setParameter("regex", $name)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findAllArray() {

        $queryBuilder = $this->createQueryBuilder('u');

        $result = $queryBuilder
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);

        return $result;
    }
}
