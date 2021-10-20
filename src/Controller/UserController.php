<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_affichage")
     */
    public function affichage(ParticipantRepository $participantRepository): Response
    {
        $membres = $participantRepository->findAll();
        return $this->render('user/affichage.html.twig', ["membres"=>$membres]);
    }


    /**
     * @Route("/user/desactiver/{id}", name="user_desactiver")
     */
    public function desactiver(
        EntityManagerInterface $entityManager,
        int $id,
        ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->find($id);
        $participant->setActif(false);

        $entityManager->persist($participant);
        $entityManager->flush();

        $this->addFlash('succes', "Membre désactivé !");

        //todo modifier la page de redirection à la validation du formulaire
        return $this->redirectToRoute('user_affichage', ["id" => $participant->getId()]);
    }

    /**
     * @Route("/user/reactiver/{id}", name="user_reactiver")
     */
    public function reactiver(
        EntityManagerInterface $entityManager,
        int $id,
        ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->find($id);
        $participant->setActif(true);

        $entityManager->persist($participant);
        $entityManager->flush();

        $this->addFlash('succes', "Membre réactivé !");

        //todo modifier la page de redirection à la validation du formulaire
        return $this->redirectToRoute('user_affichage', ["id" => $participant->getId()]);
    }

    /**
     * @Route("/user/supprimer/{id}", name="user_supprimer")
     */
    public function supprimer(
        EntityManagerInterface $entityManager,
        int $id,
        ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->find($id);

        $entityManager->remove($participant);

        $entityManager->flush();

        $this->addFlash('succes', "Membre supprimé !");

        //todo modifier la page de redirection à la validation du formulaire
        return $this->redirectToRoute('user_affichage', ["id" => $participant->getId()]);
    }

}
