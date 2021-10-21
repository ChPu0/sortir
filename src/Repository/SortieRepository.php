<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Etat;
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
        $queryBuilder->andWhere('s.nom LIKE ?1');
        $queryBuilder->setParameter(1, '%'.$criteres['nom'].'%');
        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query);
        return $paginator;
    }

    public function findByDate($criteres)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.dateHeureDebut > '.$criteres['date_min'])
                        ->andWhere('s.dateHeureDebut < '.$criteres['date_max']);
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
        $queryBuilder//->select('s')
                        //->from('sortie', 's')
                        ->innerJoin('s.inscrits', 'sp')
                        //->innerJoin('participant', 'p', 'ON', 'p.id = sp.participant_id')
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

        $em = $this->getEntityManager();
        $campusRepository = $em->getRepository(Campus::class);
        $etatRepository = $em->getRepository(Etat::class);
        $participantRepository = $em->getRepository(Participant::class);
        $sortieRepository = $em->getRepository(Sortie::class);

        //$criteres['nom'] = str_replace("'", "", $criteres['nom']) ;

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.inscrits', 'sp');
        if(!$criteres['isInscrit'] || !$criteres['isNotInscrit']) {
            if ($criteres['isInscrit']) {
                $queryBuilder->
                andWhere('sp.id = ?1');
            }
            if ($criteres['isNotInscrit']) {
                $queryBuilder->
                andWhere('sp.id != ?2');
            }
        }
        $queryBuilder->andWhere('s.campus = ?7');
        if(!$criteres['organisateur']) {
                $queryBuilder->
                    andWhere('s.organisateur != ?2');
        } else if($criteres['organisateur'] && !$criteres['isInscrit'] && !$criteres['isNotInscrit']) {
            $queryBuilder->andWhere('s.organisateur = ?2');
        }

        $queryBuilder->andWhere('s.nom LIKE ?3');

        $queryBuilder->andWhere('s.dateHeureDebut >= ?4')
        ->andWhere('s.dateHeureDebut <= ?5');
        if($criteres['etat_id'] === 5) {
            $queryBuilder->
            andWhere('s.etat = ?6')
            ->andWhere('s.dateHeureDebut < ?8');
        } else {
            $queryBuilder->
            andWhere('s.etat != ?6');
        }

        $queryBuilder->setParameter(7, $campusRepository->find($criteres['campus_id']));
        $queryBuilder->setParameter(3, '%'.$criteres['nom'].'%');
        if(!$criteres['isInscrit'] || !$criteres['isNotInscrit']) {
            if ($criteres['isInscrit']) {
                $queryBuilder->setParameter(1, $participantRepository->find($criteres['user_id']));
            }
            if ($criteres['isNotInscrit']) {
                $queryBuilder->setParameter(2, $participantRepository->find($criteres['user_id']));
            }
        }

        $queryBuilder->setParameter(4, $criteres['date_min']);
        $queryBuilder->setParameter(5, $criteres['date_max']);
        $queryBuilder->setParameter(6, $etatRepository->find(5));
        if($criteres['etat_id'] === 5) {
            $queryBuilder->setParameter(8, new \DateTime('now'));
        }
        if(!$criteres['organisateur']) {
            $queryBuilder->setParameter(2, $participantRepository->find($criteres['user_id']));
        } else if(!$criteres['isInscrit'] && !$criteres['isNotInscrit']){
            $queryBuilder->setParameter(2, $participantRepository->find($criteres['user_id']));
        }
        $query=$queryBuilder->getQuery();
        //dd($query);
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
