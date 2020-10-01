<?php

namespace App\Repository;

use App\Entity\GlobalParameters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalParameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalParameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalParameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalParametersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalParameters::class);
    }

    public function findAll()
    {
        return $this->findBy([], ['globalParameterName' => 'ASC']);
    }
}
