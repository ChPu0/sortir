<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ListSortieType;
use App\Form\SelectSortieType;
use App\Form\TargetSortieType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use ContainerQM4dqw5\getCampusRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("", name="menu")
     */
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    /**
     * @Route("/liste", name="liste")
     * @param EntityManagerInterface $entityManager
     * @param SortieRepository $sortieRepository
     * @return Response
     */
    public function findSorties(SortieRepository $sortieRepository,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                ParticipantRepository $participantRepository,
                                CampusRepository $campusRepository) : Response
    {

        //$sortiesInscrits = $sortieRepository->findByIsInscrit(1);

        $user = $participantRepository->find($this->getUser()->getId());
        $userName = $user->getPrenom() . " " . $user->getNom()[0] . ".";
        $criteres = [];
        $sorties = $sortieRepository->findAll();
        $sortiesDefaultList = $sortieRepository->findAll();

        $listSortieType = $this->createForm(ListSortieType::class);

        //$listSortieType->handleRequest($request);


        if($listSortieType->handleRequest($request)->isSubmitted() && $listSortieType->isValid()) {

            $criteresCampus = $listSortieType['campus']->getData();
            $criteresNom = $listSortieType['nom']->getData();
            $criteresDateMin = $listSortieType['dateHeureMin']->getData();
            $criteresDateMax = $listSortieType['dateHeureMax']->getData();
            $criteresOrganisateur = $listSortieType['organisateur']->getData();
            $criteresIsInscrit = $listSortieType['isInscrit']->getData();
            $criteresIsNotInscrit = $listSortieType['isNotInscrit']->getData();
            $criteresEtat = $listSortieType['etat']->getData();
            $criteresUserId = $user->getId();



            if ($criteresCampus != null) {
                $criteresCampus = $campusRepository->find($criteresCampus)->getId();
            }

            if ($criteresDateMin instanceof \DateTime) {
                $criteresDateMin = $criteresDateMin->format('Y-m-d');
            }

            if ($criteresDateMax instanceof \DateTime) {
                $criteresDateMax = $criteresDateMax->format('Y-m-d');
            }

            if($criteresEtat == true) {
                $criteresEtat = 5;
            }

            $criteres  = [
                'campus_id' => $criteresCampus,
                'nom' => $criteresNom,
                'date_min' => $criteresDateMin,
                'date_max' => $criteresDateMax,
                'user_id' => $criteresUserId,
                'organisateur' => $criteresOrganisateur,
                'etat_id' => $criteresEtat,
                'isInscrit' => $criteresIsInscrit,
                'isNotInscrit' => $criteresIsNotInscrit
            ];

            $sorties = $sortieRepository->findByCriteres($criteres);

        } else {

        }


        //dump($request);
        dump($sorties);


        return $this->render('sortie/listeSorties.html.twig', [
            'sorties' => $sorties,
            'sortiesDefaultList' => $sortiesDefaultList,
            //'sortiesInscrits' => $sortiesInscrits,
            'user' => $user,
            'userName' => $userName,
            'criteres' => $criteres,
            'listSortieType' => $listSortieType->createView()
        ]);
    }
}
