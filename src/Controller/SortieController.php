<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SelectSortieType;
use App\Form\TargetSortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function findSorties(EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();


        $selectSortieForm = $this->createForm(SelectSortieType::class);

        $sortiesInscrits = $sortieRepository->findByIsInscrit();


        return $this->render('sortie/listeSorties.html.twig', [
            "sorties" => $sorties,
            "sortiesInscrits" => $sortiesInscrits,
            'selectSortieForm' => $selectSortieForm->createView()
        ]);
    }
}
