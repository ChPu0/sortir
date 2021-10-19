<?php

namespace App\Controller;

use App\Entity\Lieu;
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
     * @Route("/lieu/modifier", name="lieu_affichage")
     */
    public function affichage(LieuRepository $lieuRepository): Response
    {
        $lieux = $lieuRepository->findAll();
        return $this->render('lieu/affichage.html.twig', ["lieux"=>$lieux]);
    }

    /**
     * @Route("/lieu/supprimer/{id}", name="lieu_supprimer")
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

        //todo modifier la page de redirection à la validation du formulaire
        return $this->redirectToRoute('lieu_affichage', ["id" => $lieu->getId()]);
    }


}
