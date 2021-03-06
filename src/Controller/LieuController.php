<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuSearchType;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Services\CallAPI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu/ajout", name="lieu_ajout")
     */
    public function ajout(EntityManagerInterface $entityManager, Request $request): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('succes', 'Le lieu a bien été ajouté');
            //todo modifier la page de redirection à la validation du formulaire
            return $this->redirectToRoute('lieu_affichage');
        }

        return $this->render('lieu/ajoutLieu.html.twig', ['lieuForm'=>$lieuForm->createView()]);
    }

    /**
     * @Route("/lieu/afficher", name="lieu_affichage")
     */
    public function affichage(LieuRepository $lieuRepository, EntityManagerInterface $em, Request $request): Response
    {
        $searchForm = $this->createForm(LieuSearchType::class);

        if($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $critere = $searchForm['nom']->getData();
            $resultat = $lieuRepository->findByName($critere);

        }
        else {
            $resultat = $em->getRepository(Lieu::class)->findAll();
        }

        return $this->render('lieu/affichage.html.twig', [
            'page_name' => 'Lieux',
            'lieux' => $resultat,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/lieu/supprimer/admin/{id}", name="lieu_supprimer")
     */
    public function supprimer(
        EntityManagerInterface $entityManager,
        int $id,
        LieuRepository $lieuRepository)
    {
        $lieu = $lieuRepository->find($id);

        $entityManager->remove($lieu);

        $entityManager->flush();

        $this->addFlash('succes', "Lieu supprimé !");

        return $this->redirectToRoute('lieu_affichage', ["id" => $lieu->getId()]);
    }

    /**
     * @Route("/lieu/editer/{id}", name="lieu_edit")
     */
    public function edit(Lieu $lieu, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->remove('send');
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $lieu = $form->getData();

            $em->persist($lieu);
            $em->flush();
            $this->addFlash('succes', 'Le lieu a été modifié !');

            $this->lieuxListe = $em->getRepository(Lieu::class)->findAll();

            return $this->redirectToRoute('lieu_affichage');
        }

        return $this->render('lieu/edition.html.twig', [
            'page_name' => 'Edition Lieu',
            'lieu' => $lieu,
            'form' => $form->createView()
        ]);
    }

}
