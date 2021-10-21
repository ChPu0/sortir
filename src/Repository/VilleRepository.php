<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }


    public function findByName($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder->andWhere('v.nom LIKE ?1')
                    ->orWhere('v.codePostal LIKE ?1');
        $queryBuilder->setParameter(1, '%'.$criteres.'%');
        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }
}
