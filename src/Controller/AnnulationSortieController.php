<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnulationSortieController extends AbstractController
{
    /**
     * @Route("/annulation/sortie/{id}", name="annulation_sortie")
     */
    public function annulation(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $aujourdhui = new \DateTime('now');
        //Récupere la sortie à annuler
        $sortieSelected = $sortieRepository->find($id);

        //en cas de saisie de num sortie inexistant
        if(!$sortieSelected) {
            $this->addFlash('error', "La sortie n'existe pas");
            return $this->render('error/error.html.twig');
        }
        elseif ($aujourdhui > $sortieSelected->getDateHeureDebut()) {
            $this->addFlash('error', "La date de sortie est passée, impossible de la modifier");
            return $this->render('error/error.html.twig');
        }
        else {
            //Modiife l'état de la sortie
            $sortieSelected->getEtat()->setLibelle("Annulé");
            //Crée le formulaire de Motif d'annulation
            $annulationForm = $this->createForm(AnnulationType::class, $sortieSelected);
            $annulationForm->handleRequest($request);

            if ($annulationForm->isSubmitted() && $annulationForm->isValid()) {
                $entityManager->persist($sortieSelected);
                $entityManager->flush();

                $this->addFlash('succes', 'Sortie annulée');
            }
        }
        return $this->render('annulation_sortie/annulation.html.twig',  ["sortie"=>$sortieSelected, "annulationForm"=>$annulationForm->createView()]);
    }
}
