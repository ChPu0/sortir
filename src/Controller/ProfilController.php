<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilController extends AbstractController
{
    //-----------------------------------------
    //-----Fonction qui hash les mots de passe
    //-----------------------------------------

    private $passwordHasher;

     public function __construct(UserPasswordHasherInterface $passwordHasher)
     {
         $this->passwordHasher = $passwordHasher;
     }




    //-----------------------------------------
    // Fonction de création d'un nouvel utilisateur
    //-----------------------------------------

    /**
     * @Route("/profil/create", name="profil_create")
     */
    public function create(
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger) {

        //Formulaire de saisie d'un profil User
            $participant = new Participant();

            //todo Definition des infos par défaut
            $participant->setRoles(["ROLE_USER"]);
            //$participant->setActif(true);
            //$participant->setAdministrateur(false);
            $participant->setImgProfil('avatar.png');

            $profilForm = $this->createForm(ProfilType::class, $participant);

            //Intégration Formulaire dans DB
            $profilForm->handleRequest($request);
            if ($profilForm->isSubmitted() && $profilForm->isValid()) {
                //Hash du mot de passe
                $pass =  $profilForm->get('password')->getData();
                $participant->setPassword($this->passwordHasher->hashPassword($participant, $pass));
                //Definition des rôles
                if($participant->getAdministrateur() === true) {
                    $participant->setRoles(["ROLE_ADMIN"]);
                }
                else{
                    $participant->setRoles(["ROLE_USER"]);
                }
                //Upload image profil
                $imgProfil = $profilForm->get('image')->getData();
                if ($imgProfil) {
                    $originalFilename = pathinfo($imgProfil->getClientOriginalName(), PATHINFO_FILENAME);
                    // include le nom du fichier dans l'url
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imgProfil->guessExtension();
                    // Déplace le fichier dans le bon dossier (config yaml)
                    try {
                        $imgProfil->move(
                            $this->getParameter('img_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        //genere une erreur
                    }

                    // remplace le fichier si existant
                    $participant->setImgProfil($newFilename);
                }




                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('succes', "Profil mis à jour !");

                return $this->redirectToRoute('profil_show', ["id" => $participant->getId()]);
            }
        return $this->render('profil/createProfil.html.twig', ["profilForm" => $profilForm->createView()]);
    }


    //--------------------------------------
    //Formulaire d'affichage d'un profil User
    //------------------------------------------


    /**
     * @Route("/profil/show/{id}", name="profil_show")
     */
    public function show(int $id, ParticipantRepository $participantRepository) {

        $participant = $participantRepository->find($id);
        if(!$participant) {
            throw $this->createNotFoundException('Participant inexistant');
        }
        return $this->render('profil/showProfil.html.twig', ["participant" => $participant]);
    }



    //------------------------------------------
    //Formulaire de modif d'un profil User
    //------------------------------------------

    /**
     * @Route("/profil/amend/{id}", name="profil_amend")
     */
    public function amend(
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        int $id,
        ParticipantRepository $participantRepository,
        SluggerInterface $slugger)
    {

        //Formulaire de saisie d'un profil User
        $participant = new Participant();
        $participant = $participantRepository->find($id);

        $profilForm = $this->createForm(ProfilType::class, $participant);

        //Intégration Formulaire dans DB
        $profilForm->handleRequest($request);
        if ($profilForm->isSubmitted() && $profilForm->isValid()) {
            //Hash du mot de passe
            $pass = $profilForm->get('password')->getData();
            $participant->setPassword($this->passwordHasher->hashPassword($participant, $pass));
            //Upload image profil
            $imgProfil = $profilForm->get('image')->getData();
            if ($imgProfil) {
                $originalFilename = pathinfo($imgProfil->getClientOriginalName(), PATHINFO_FILENAME);
                // include le nom du fichier dans l'url
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imgProfil->guessExtension();
                // Déplace le fichier dans le bon dossier (config yaml)
                try {
                    $imgProfil->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    //genere une erreur
                }

                // remplace le fichier si existant
                $participant->setImgProfil($newFilename);
            }

        $entityManager->persist($participant);
        $entityManager->flush();

        $this->addFlash('succes', "Profil mis à jour !");

        return $this->redirectToRoute('profil_show', ["id" => $participant->getId()]);
    }

        return $this->render('profil/createProfil.html.twig', ["profilForm" => $profilForm->createView()]);
    }


    //todo a adapter au formulaire d'Anaïs
    /**
     * @Route("/desinscription/{id}", name="profil_desinscription")
     */
    //------------------------------------
    //Retirer un utilisateur d'une sortie
    //-----------------------------------
    public function desinscription(int $id, EntityManagerInterface $entityManager,SortieRepository $sortieRepository, ParticipantRepository $participantRepository) {
        $connectedUser = $this->getUser();
        $connectedUserId = $connectedUser->getId();

        $selectedUser = $participantRepository->find($connectedUserId);
        $selectedSortie = $sortieRepository->find($id);

        if($selectedSortie != null) {

            $selectedUser->getSorties()->removeElement($selectedSortie);
            $selectedSortie->getInscrits()->removeElement($selectedUser);

            $entityManager->flush();

            $this->addFlash('succes', "Vous vous etes désinscrit !");
            return $this->redirectToRoute('profil_show', ["id" => $connectedUserId]);
        }
        else {
            $this->addFlash('error', "Cette activité n'existe pas");
            return $this->redirectToRoute('profil_show', ["id" => $connectedUserId]);

        }



    }

}