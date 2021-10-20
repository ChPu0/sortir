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
use Symfony\Component\Mailer\Transport\Smtp\Stream\AbstractStream;

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

    public function findByCampus($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->innerJoin('s.campus', 'sc')
                    ->andWhere('sc.id = ' . $criteres['campus_id']);

        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByName($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere("s.nom LIKE '%" . $criteres['nom'] . "%'");
        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByDate($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.dateHeureDebut > ' . $criteres['date_min'])
                        ->andWhere('s.dateHeureDebut < ' . $criteres['date_max']);


        $query = $queryBuilder->getQuery();
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByOrganisateur($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.organisateur = ' . $criteres['organisateur']);
        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByIsInscrit($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder->innerJoin('s.inscrits', 'sp')
                        ->andWhere('sp.id = ' . $criteres['user_id']);

        //Table relationnelle sortie_participant :
        /*$entityManager = $this->getEntityManager();
        $dql = "SELECT * FROM sortie as s
                INNER JOIN sortie_participant as sp
                ON s.id = sp.sortie_id
                INNER JOIN participant as p
                ON p.id = sp.participant_id
                WHERE sp.participant_id = " . $criteres['user_id'];

        $query = $entityManager->createQuery($dql);*/

        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByEtatMaxOneMonth($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.etat = ' . $criteres['etat'])
                        ->andWhere(s.date_heure_debut < NOW() AND DATEDIFF(NOW(), s.date_heure_debut) <= 30);
        $query = $queryBuilder->getQuery();

        $query->setMaxResults(30);
        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByCriteres($criteres)  {

       $queryBuilder = $this->createQueryBuilder('s');
       $queryBuilder->where('s.campus = ' . $criteres['campus'])
                       ->andWhere('s.nom LIKE %' . $criteres['nom'] . '%')
                       ->andWhere('s.date_heure_debut > ' . $criteres['date_min'] . ' AND s.date_heure_debut < ' . $criteres['date_max'])
                       ->andWhere('date_heure_debut < NOW() AND DATEDIFF(NOW(), date_heure_debut) <= 30')
                       ->andWhere('s.organisateur = ' . $criteres['organisateur'])
                       ->andWhere('s.etat = ' . $criteres['etat'])

                       ->innerJoin('s.inscrits', 'sp')
                       ->innerJoin('participant', 'p', 'ON', 'p.id = sp.participant_id')
                       ->andWhere('sp.id = ' . $criteres['user_id']);
       $query = $queryBuilder->getQuery();

        $criteres['nom'] = str_replace("'", "", $criteres['nom']) ;

        //En DQL :
        /*$entityManager = $this->getEntityManager();
        $rsm = new ResultSetMapping();

        $dql = "SELECT * FROM sortie s
                WHERE s.campus_id = ?
                AND s.nom LIKE '%'?'%'
                AND s.organisateur_id = ?
                AND s.date_heure_debut >= ?
                AND s.date_heure_debut <= ?
                AND s.etat_id = ?
                AND s.date_heure_debut < NOW() AND DATEDIFF(NOW(), s.date_heure_debut) <= 30
                INNER JOIN sortie_participant as sp
                ON sp.sortie_id = s.id
                AND sp.participant_id = ?
                INNER JOIN participant as p
                ON p.id = sp.participant_id
                ";


        $query = $entityManager->createNativeQuery($dql, $rsm);

        $query->setParameter(1, $criteres['campus_id']);
        $query->setParameter(2, $criteres['nom']);
        $query->setParameter(3, $criteres['user_id']);
        $query->setParameter(4, $criteres['date_min']);
        $query->setParameter(5, $criteres['date_max']);
        $query->setParameter(6, $criteres['etat_id']);
        $query->setParameter(7, $criteres['user_id']);*/

        return $query->getResult();

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
