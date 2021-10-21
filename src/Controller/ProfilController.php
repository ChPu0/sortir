<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    // Fonction de d'affiche de tous les USERS
    //-----------------------------------------

    /**
     * @Route("/profil", name="profil_affichage")
     */
    public function affichage(ParticipantRepository $participantRepository): Response
    {
        $membres = $participantRepository->findAll();
        return $this->render('profil/affichageProfil.html.twig', ["membres"=>$membres]);
    }


    //-----------------------------------------
    // Fonction de création d'un nouvel utilisateur
    //-----------------------------------------

    /**
     * @Route("/admin/profil/create", name="profil_create")
     */
    public function create(
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger) {

        //Formulaire de saisie d'un profil User
            $participant = new Participant();

            //todo Definition des infos par défaut
            $participant->setImgProfil('avatar.png');

            $profilForm = $this->createForm(ProfilType::class, $participant);

            //Intégration Formulaire dans DB
            $profilForm->handleRequest($request);
            if ($profilForm->isSubmitted() && $profilForm->isValid()) {
                //Hash du mot de passe
                $pass =  $profilForm->get('password')->getData();
                $participant->setPassword($this->passwordHasher->hashPassword($participant, $pass));
                //Definition des roles
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
            $this->addFlash('error', "Ce membre n'existe pas");
            return $this->render('error/error.html.twig');
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

        //todo modifier la page de redirection à la validation du formulaire
        return $this->redirectToRoute('profil_show', ["id" => $participant->getId()]);
    }

        return $this->render('profil/amendProfil.html.twig', ["profilForm" => $profilForm->createView(), "img" =>$participant->getImgProfil()]);
    }

    /**
     * @Route("/profil/desactiver/{id}", name="profil_desactiver")
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
        return $this->redirectToRoute('profil_affichage', ["id" => $participant->getId()]);
    }

    /**
     * @Route("/profil/reactiver/{id}", name="profil_reactiver")
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
        return $this->redirectToRoute('profil_affichage', ["id" => $participant->getId()]);
    }


    //---------------------------------------------------------
    //Suppression d'un profil
    //-----------------------------------------------------------

    /**
     * @Route("/profil/supprimer/{id}", name="profil_supprimer")
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
        return $this->redirectToRoute('profil_affichage', ["id" => $participant->getId()]);
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

            //todo modifier la page de redirection à la validation du formulaire
            return $this->redirectToRoute('profil_show', ["id" => $connectedUserId]);
        }
        else {
            $this->addFlash('error', "Cette activité n'existe pas");
            return $this->render('error/error.list.html.twig');
        }

    }

    /**
     * @Route("/profil/add/csv", name="profil_csv")
     * @throws Exception
     */
    public function ajouterParticipantCSV(Request $request,
                                          EntityManagerInterface $entityManager,
                                          ParticipantRepository $participantRepository,
                                          ValidatorInterface $validator,
                                          CampusRepository $campusRepository){

        //Si le fichier a été ajouté
        if ($request->isMethod('POST'))
        {
            //On récupère les données du fichier csv en indiquant les en-têtes
            $csv = Reader::createfromPath($request->files->get('csvFile')->getRealPath())
                ->setHeaderOffset(0);
            $i = 0;
            //On récupère l'encodeur pour hasher les mots de passe
            $encoder = $this->passwordHasher;

            //Pour chaque ligne de notre fichier, on crée un utilisateur
            foreach($csv as $record){
                $i++;
                $participant = new Participant();

                //On vérifie qu'il n'existe pas déjà
                if($participantRepository->findOneBy(["email" => $record['email']]) === null ){
                    //On ajoute les valeurs de base au participant
                    $participant->setEmail($record['email']);
                    $participant->setNom($record['nom']);
                    $participant->setPrenom($record['prenom']);
                    $participant->setPassword($encoder->hashPassword($participant, $record['password']));
                    $participant->setTelephone($record['telephone']);
                    $participant->setActif($record['actif']);

                    //Le pseudo par défaut sera l'adresse email
                    $participant->setPseudo($record['email']);

                    //On met une image de profil par défaut
                    $participant->setImgProfil('avatar.png');

                    //On vérifie si c'est un administrateur
                    if($record['administrateur'] === 'false' || $record['administrateur'] === 'non'){
                        $participant->setAdministrateur(false);
                        $participant->setRoles(["ROLE_USER"]);
                    } else {
                        $participant->setAdministrateur($record['administrateur']);
                        $participant->setRoles(["ROLE_ADMIN"]);
                    }

                    //On va chercher le campus pour le rajouter
                    $campus = $campusRepository->findOneBy(['nom' => $record['campus']]);
                    $participant->setCampus($campus);

                    //On va le participant et renvoyer une erreur s'il n'est pas valide
                    $errors = $validator->validate($participant);
                    if($errors->count() > 0){
                        $this->addFlash('error', "Erreur ligne " . $i . " : des contraintes ne sont pas respectées. Cet utilisateur ne sera pas ajouté à la base de données.");
                    } else {
                        //S'il n'y a pas d'erreur de validation, on peut l'envoyer à la base de données
                        $entityManager->persist($participant);
                    }
                } else {
                    $this->addFlash('error', "Erreur ligne " . $i . " : cet email existe déjà");
                }

            }
            $entityManager->flush();
        }

        //Renvoie vers le formulaire d'ajout d'un fichier CSV
        return $this->render('csv/uploadCSV.html.twig');
    }

}