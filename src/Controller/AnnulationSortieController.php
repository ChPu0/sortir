<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Repository\EtatRepository;
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
    public function annulation(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request,
                                    EtatRepository $etatRepository): Response
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
            //Crée le formulaire de Motif d'annulation
            $annulationForm = $this->createForm(AnnulationType::class, $sortieSelected);
            $annulationForm->handleRequest($request);

            if ($annulationForm->isSubmitted() && $annulationForm->isValid()) {
                //Modiife l'état de la sortie
                $sortieSelected->setEtat($etatRepository->find(6));
                $entityManager->flush();

                $this->addFlash('succes', 'Sortie annulée');
                //todo ajouter une page de redirection à la validation du formulaire

                return $this->redirectToRoute('sortie_liste');
            }
        }
        return $this->render('annulation_sortie/annulation.html.twig',  ["sortie"=>$sortieSelected, "annulationForm"=>$annulationForm->createView()]);
    }

}
