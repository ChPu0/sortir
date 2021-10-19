<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\ListSortieType;
use App\Form\SelectSortieType;
use App\Form\TargetSortieType;
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
                                Request $request) : Response
    {

        //$sortiesInscrits = $sortieRepository->findByIsInscrit(1);
        $user = $this->getUser();

        //$sorties = $sortieRepository->findAll();


        $listSortieType = $this->createForm(ListSortieType::class);

        $listSortieType->handleRequest($request);


        if($listSortieType->isSubmitted()) {

            //array_push($criteres, $this->getUser()->getUserIdentifier());
            //dd($criteres);
            $criteres  = $listSortieType->getData();
            $criteresLieu  = $listSortieType['campus']->getData();

            $sorties = $sortieRepository->findBy($criteres);
        } else {
            $sorties = $sortieRepository->findAll();
        }


        dump($request);


        return $this->render('sortie/listeSorties.html.twig', [
            'sorties' => $sorties,
            //'sortiesInscrits' => $sortiesInscrits,
            'user' => $user,
            'listSortieType' => $listSortieType->createView()
        ]);
    }
}
