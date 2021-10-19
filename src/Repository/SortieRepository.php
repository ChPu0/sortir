<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByCampus()
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->innerJoin('s.campus', 'sc')
                    ->andWhere('sc.id = 1');

        $query = $queryBuilder->getQuery();
        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByName($nom)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.nom LIKE %' . $nom . '%');
        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByDate(DateTimeType $dateMin, DateTimeType $dateMax)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.date_heure_debut > ' . $dateMin . 'AND s.date_heure_debut < ' . $dateMax);
        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByOrganisateur($organisateur)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.organisateur = ' . $organisateur);
        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByIsInscrit($id)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder//->select('s')
                        //->from('sortie', 's')
                        ->innerJoin('s.inscrits', 'sp')
                        //->innerJoin('participant', 'p', 'ON', 'p.id = sp.participant_id')
                        ->andWhere('sp.id = ' . $id);

        //Table relationnelle sortie_participant :
        /*$entityManager = $this->getEntityManager();
        $dql = "SELECT s FROM App\Entity\Sortie as s
                INNER JOIN sortie_participant 
                as sp ON s.id = sp.sortie_id 
                INNER JOIN participant as p ON p.id = sp.participant_id 
                WHERE sp.participant_id = 2";

        //$query = $entityManager->createQuery($dql);*/

        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;

        /*$sql = "SELECT * FROM sortie as s
                INNER JOIN sortie_participant 
                as sp ON s.id = sp.sortie_id 
                INNER JOIN participant as p ON p.id = sp.participant_id 
                WHERE sp.participant_id = 1";

        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $query = $em->createNativeQuery($sql, $rsm);

        $sorties = $query->getResult();

        //dd($sorties);
        //dump($query);

        return $sorties;*/
    }

    public function findByEtatMaxOneMonth($etat)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.etat = ' . $etat)
                        ->andWhere('s.date_part("day",age(date_heure_debut, NOW()) <= 30 ');
        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByCriteres($criteres)  {


       $queryBuilder = $this->createQueryBuilder('s')
                                ->innerJoin('s.campus', 'sc')
                                ->andWhere('sc.nom = ' . $criteres['campus'])
                                ->andWhere('s.nom LIKE %' . $criteres['nom'] . '%')
                                ->andWhere('s.organisateur = ' . $criteres['organisateur'])
                                ->andWhere('s.date_heure_debut > ' . $criteres['date_min'] . 'AND s.date_heure_debut < ' . $criteres['date_max'])
                                ->innerJoin('s.inscrits', 'sp')
                                ->innerJoin('participant', 'p', 'ON', 'p.id = sp.participant_id')
                                ->andWhere('sp.id = ' . $criteres['id_user'])
                                ->andWhere('s.etat = ' . $criteres['etat'])
                                ->andWhere('s.date_part("day",age(date_heure_debut, NOW()) <= 30 ');
       $query = $queryBuilder->getQuery();

        //En DQL :
        /*$entityManager = $this->getEntityManager();
        $dql = "
                SELECT s 
                FROM App\Entity\Sortie s 
                WHERE s.nom LIKE % ". $criteres['nom'] . "%" .
                "AND s.organisateur = ". $criteres['organisateur'] .
                "AND s.date_heure_debut > " . date('YYYY-mm-dd', $criteres['date_heure_debut']) . "AND s.date_heure_debut < " . date('YYYY-mm-dd', $criteres['date_heure_fin']) .
                "INNER JOIN 's.campus', 'sc'
                WHERE 'sc.id' = " . $criteres['campus']->getNom() .
             "AND WHERE 's.etat = " . $criteres['etat'] .
                "AND WHERE 's.date_part('day',age(date_heure_debut, NOW()) <= 30 '
                ";

        $query = $entityManager->createQuery($dql);*/

        return new Paginator($query);
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
