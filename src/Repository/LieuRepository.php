<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lieu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lieu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lieu[]    findAll()
 * @method Lieu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function findByName($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('l');
        $queryBuilder->innerJoin('l.ville', 'lv' );
        $queryBuilder->andWhere('l.nom LIKE ?1')
            ->orWhere('lv.nom LIKE ?1')
            ->orWhere('l.longitude LIKE ?1')
            ->orWhere('l.latitude LIKE ?1')
            ->orWhere('l.rue LIKE ?1');



        $queryBuilder->setParameter(1, '%'.$criteres.'%');
        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }
}
