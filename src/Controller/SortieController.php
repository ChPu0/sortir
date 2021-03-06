<?php

namespace App\Controller;


use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\CreeSortieType;
use App\Form\LieuType;
use App\Form\ListSortieType;
use App\Form\ModifySortieType;
use App\Form\SelectSortieType;
use App\Form\TargetSortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use ContainerQM4dqw5\getCampusRepositoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="sortie_")
 */
class SortieController extends AbstractController
{


    /**

     * @Route("/sortie/add", name="sortie_add")
     */
    public function add(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository)
    {
        $sortie = new Sortie();
        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);
        $form = $this->createForm(CreeSortieType::class, $sortie);
        $form -> handleRequest($request);

        $listVille = $em->getRepository(Ville::class)->findAll();

        if($formLieu->isSubmitted() && $formLieu->isValid()){
            $lieu = $formLieu->getData();
            $sortie = $form->getData();
            $formResend = $this->createForm(ModifySortieType::class, $sortie);
            $formResend -> handleRequest($request);

            $em->persist($lieu);
            $em->flush();
            $this->addFlash('succes', 'Le lieu a été ajouté !');

        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();

            if( $form->get('save')->isClicked()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Créée']);
                $sortie->setEtat($etat);
            } else if( $form->get('publish')->isClicked()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Ouverte']);
                $sortie->setEtat($etat);

            }else{
                return $this->redirectToRoute('sortie_liste');
            }

            $sortie->setOrganisateur($this->getUser());

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('succes', 'La sortie a été ajoutée !');
            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/createSortie.html.twig', [
            'page_name' => 'Créer une sortie',
            'form' => $form->createView(),
            'formLieu'=>$formLieu->createView(),
            'listVille'=>$listVille
        ]);
    }

    /**
     * @Route("/modifSortie/{id}", name="modif_sortie")
     */
    public function modifSortie(Sortie $sortie,
                                Request $request,
                                EntityManagerInterface $em,
                                EtatRepository $etatRepository,
                                SortieRepository $sortieRepository,
                                LieuRepository $lieuRepository)
    {

        $sortie = $sortieRepository->find($sortie->getId());
        $lieu = $lieuRepository->find($sortie->getLieu()->getId());

        $form = $this->createForm(ModifySortieType::class, $sortie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $sortie = $form->getData();

            if( $form->get('save')->isClicked()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Créée']);
                $sortie->setEtat($etat);

            }elseif( $form->get('publish')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }elseif ($form->get('Annuler')->isClicked()) {
                return $this->redirectToRoute('sortie_liste');
            }
            elseif( $form->get('delete')->isClicked()) {
                return $this->redirectToRoute('annulation_sortie', ['id'=> $sortie->getId()]);
            }else{
                return $this->redirectToRoute('sortie_liste');
            }

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('succes', 'La sortie a été modifiée !');

            $this->sortiesListe = $em->getRepository(Sortie::class)->findAll();

            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/edit.html.twig', [
            'page_name' => 'Modifier une sortie',
            'sortie' => $sortie,
            'lieu' => $lieu,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/sortie/afficher/{id}", name="afficher_sortie")
     */
    public function afficher(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepository):Response

    {
        $sortieData = $this->sortiesListe = $em->getRepository(Sortie::class)->find($request->get('id'));
        $participant = $sortieData->getInscrits();

        return $this->render('sortie/afficherSortie.html.twig', [
            'page_name' => 'Description Sortie',
            'sortie' => $sortieData,
            'participants' => $participant

        ]);

    }


    /**
     * @Route("/", name="liste")
     * @param EntityManagerInterface $entityManager
     * @param SortieRepository $sortieRepository
     * @return Response
     */
    public function findSorties(SortieRepository $sortieRepository,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                ParticipantRepository $participantRepository,
                                CampusRepository $campusRepository,
                                EtatRepository $etatRepository) : Response
    {

        $toutesSorties = $sortieRepository->findAll();
        $aujourdhui = new \DateTime('now');
        foreach ($toutesSorties as $sortie) {
            $date = $sortie->getDateHeureDebut();

            // $dateCopie = new \DateTime();
            $dateCopie = &$date;
            $duree = $sortie->getDuree();

            if($duree == null) {
                $duree = 1;
            }

           $interval = new \DateInterval('PT'.$duree.'M');
           $finInscription = $sortie->getDateLimiteInscription();


            //$diff = date_diff($aujourdhui, $date);

            if($finInscription < $aujourdhui) { //Inscription finie
                if($date <$aujourdhui) {
                    $dateFin = $dateCopie->add($interval);
                    if ($dateFin > $aujourdhui) {
                        $sortie->setEtat($etatRepository->findOneBy(['id' => 4])); // ENCOURS
                        $dateCopie->sub($interval);
                    } else {
                        $sortie->setEtat($etatRepository->findOneBy(['id' => 5])); //PASSEE
                        $dateCopie->sub($interval);
                    }
                }
                else{
                    $sortie->setEtat($etatRepository->findOneBy(['id'=>3])); //CLOTUREE
                }
            }
     //       $interval->invert=1;


        }
        $entityManager->flush();

        $user = $participantRepository->find($this->getUser()->getId());
        //$userName = $user->getPrenom() . " " . $user->getNom()[0] . ".";
        $campusDefault = $campusRepository->find($user->getCampus()->getId());
        $criteres = ["campus_id" => $user->getCampus()->getId(),
                      "nom" => '',
                      "date_min" => "1800-01-01",
                      "date_max" => "2999-12-31",
                      "user_id" => $user->getId(),
                      "organisateur" => true,
                      "etat_id" => false,
                      "isInscrit" => true,
                      "isNotInscrit" => true];

        $sorties = $sortieRepository->findByCriteres($criteres);

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
            } else {
                $criteresDateMin = \DateTime::createFromFormat('Y-m-d', '1800-01-01');
                $criteresDateMin = $criteresDateMin->format('Y-m-d');
            }

            if ($criteresDateMax instanceof \DateTime) {
                $criteresDateMax = $criteresDateMax->format('Y-m-d');
            } else {
                $criteresDateMax = \DateTime::createFromFormat('Y-m-d', '2999-12-31');
                $criteresDateMax = $criteresDateMax->format('Y-m-d');
            }

            if($criteresDateMax < $criteresDateMin){
                $this->addFlash('error', "La date maximum doit être supérieure à la date minimum.");

                $this->redirectToRoute('sortie_liste');
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

        }

        return $this->render('sortie/listeSorties.html.twig', [
            'sorties' => $sorties,
            'campus' => $campusDefault,
            'user' => $user,
            'criteres' => $criteres,
            'listSortieType' => $listSortieType->createView()
        ]);
    }

    /**
     * @Route("/editEtat/{id}", name="editEtat")
     * @param int $id
     */
    public function editEtat(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager) {

        $sortie = $sortieRepository->find($id);

        $sortie->setEtat($etatRepository->find(2));

        $entityManager->flush();

        return $this->redirectToRoute('sortie_liste');

    }

    /**
     * @Route("/desinscription/{id}", name="desinscription")
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
            return $this->redirectToRoute('sortie_liste');


        }
        else {
            $this->addFlash('error', "Cette activité n'existe pas");
            return $this->render('error/error.html.twig');
        }

    }

    /**
     * @Route("/add/{id}", name="sortie_add_participant")
     */
    public function ajouterParticipant(int $id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository,Request $request, EntityManagerInterface $entityManager)
    {
        $sortie = $sortieRepository->find($id);
        $today = new \DateTime('now');


        //Si la sortie n'est pas trouvée ou si elle est complète
        if(!$sortie ||
            ($sortie->getInscrits()->count() == $sortie->getNbInscriptionsMax() && $sortie->getNbInscriptionsMax() != null)){
            $this->addFlash('error', "La sortie n'existe plus ou est complète");
            return $this->redirectToRoute(
                'sortie_liste'
            );
            //Si la date d'inscription est dépassée
        } else if($today >= $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', "Impossible de s'inscrire, la date d'inscription est dépassée");
            return $this->redirectToRoute(
                'sortie_liste'
            );
        }

        //On récupère le participant qui souhaite s'inscrire
        $participant = $this->getUser();
        $participant = $participantRepository->find($participant->getId());

        //On vérifie s'il est déjà inscrit
        if($sortie->getInscrits()->contains($participant)){
            $this->addFlash('error', "Vous êtes déjà inscrits à cette sortie");
            return $this->redirectToRoute(
                'sortie_liste'
            );
            //S'il n'est pas inscrit, on l'ajoute à la sortie
        } else {
            $sortie->addInscrit($participant);
            $participant->addSorty($sortie);
            $entityManager->flush();

            $this->addFlash('succes', "Vous êtes inscrits à la sortie " . $sortie->getNom() . "!");
            return $this->redirectToRoute(
                'sortie_liste'
            );
        }
    }
}

