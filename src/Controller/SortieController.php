<?php

namespace App\Controller;


use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ListSortieType;
use App\Form\SelectSortieType;
use App\Form\TargetSortieType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
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

     * @Route("/sortie/add", name="sortie_add")
     */
    public function add(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository)
    {
        $sortie = new Sortie();
        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);
        $form = $this->createForm(ModifySortieType::class, $sortie);
        $form -> handleRequest($request);

        $listVille = $em->getRepository(Ville::class)->findAll();

        if($formLieu->isSubmitted() && $formLieu->isValid()){
            $lieu = $formLieu->getData();
            $sortie = $form->getData();
            $formResend = $this->createForm(ModifySortieType::class, $sortie);
            $formResend -> handleRequest($request);

            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Le lieu a été ajouté !');

        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie = $form->getData();


/**
            $datedebut = $form['dateHeureDebut']->getData();
            $sortie->setDateHeureDebut(\DateTime::createFromFormat('Y/m/d H:i', $datedebut));

            $datecloture = $form['datecloture']->getData();
            $sortie->setDatecloture(\DateTime::createFromFormat('Y/m/d', $datecloture));



                $sortie->setEtatSortie("En création");
            }elseif( $form->get('publish')->isSubmitted()){
                $sortie->setEtatSortie("Ouvert");

*/
            if( $form->get('save')->isSubmitted()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Créée']);
                $sortie->setEtat($etat);

            }elseif( $form->get('publish')->isSubmitted()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Ouverte']);
                $sortie->setEtat($etat);

            }else{
                return $this->redirectToRoute('main_home');
            }

            $sortie->setOrganisateur($this->getUser());

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a été ajoutée !');
            return $this->redirectToRoute('sortie');
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
    public function modifSortie(Sortie $sortie, Request $request, EntityManagerInterface $em, EtatRepository $etatRepository)
    {
        $form = $this->createForm(ModifySortieType::class, $sortie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $sortie = $form->getData();

            if( $form->get('save')->isSubmitted()){
                $etat = $etatRepository->findOneBy(['libelle'=>'Créée']);
                $sortie->setEtat($etat);

            }elseif( $form->get('publish')->isSubmitted()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }elseif ($form->get('Annuler')->isSubmitted()){
                return $this->redirectToRoute('sortie');

            }else{
                return $this->redirectToRoute('sortie');
            }

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a été modifiée !');

            $this->sortiesListe = $em->getRepository(Sortie::class)->findAll();

            return $this->redirectToRoute('sortie');
        }

        return $this->render('sortie/edit.html.twig', [
            'page_name' => 'Modifier une sortie',
            'sortie' => $sortie,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/sortie/afficher/{id}", name="afficher_sortie")
     */
    public function afficher(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepository):Response

    {
        $sortieData = $this->sortiesListe = $em->getRepository(Sortie::class)->find($request->get('id'));
        $participant = $participantRepository->find($id);

        return $this->render('sortie/afficherSortie.html.twig', [
            'page_name' => 'Description Sortie',
            'sortie' => $sortieData,
            'participant' => $participant

        ]);

    }
    /**
     * @Route("/sortie/addParticipant/{id}", name="add_participant_sortie")
     */
    public function add_participant(EntityManagerInterface $em, Request $request, Sortie $sortie):Response
    {

        $participant = $em->getRepository(Participant::class)->find($this->getUser()->getId());
        $inscription = new Inscriptions();
        $inscription->setDateInscription(new \DateTime());
        $inscription->setSortie($sortie);
        $inscription->setParticipant($participant);

        $em->persist($inscription);


        $em->flush();
        $this->addFlash('success', 'L\'inscription a été faite !');
        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/sortie/removeParticipant/{id}", name="remove_participant_sortie")
     */
    public function remove_participant(EntityManagerInterface $em, Request $request): Response{
        $participant = $this->getUser();

        $sortie = $em->getRepository(Sortie::class)->find($request->get('id'));

        $inscription = $em->getRepository(Inscriptions::class)->findBy(['sortie'=>$sortie->getId(), 'participant'=>$participant->getId()],['sortie'=>'ASC']);
        $em->remove($inscription[0]);
        $em->flush();
        $this->addFlash('success', 'L\'inscription a été retirée !');
        return $this->redirectToRoute('sortie_add_participant');
    }

    /**
     * @Route("/sortie/annuler/{id}", name="annuler_sortie")
     */
    public function annuler_sortie(Request $request, EntityManagerInterface $em, Sortie $sortie){

        $participant = $this->getUser();

        $form = $this->createForm(AnnulationType::class, $sortie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $sortie->setInfosSortie($form['infosSortie']->getData());
            $sortie=$form->setEtat();
            $em->flush();
            $this->addFlash('success', 'La sortie a été annulée !');

            $this->sortiesListe = $em->getRepository(Sortie::class)->findAll();

            return $this->redirectToRoute('main_home');
        }
       return $this->render('sortie/annuler.html.twig', [
            'page_name' => 'Annuler Sortie',
            'sortie' => $sortie,
            'participants' => $participant,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/liste", name="liste")
     * @param EntityManagerInterface $entityManager
     * @param SortieRepository $sortieRepository
     * @return Response
     */
    public function findSorties(SortieRepository $sortieRepository,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                ParticipantRepository $participantRepository,
                                CampusRepository $campusRepository) : Response
    {

        //$sortiesInscrits = $sortieRepository->findByIsInscrit(1);

        $user = $participantRepository->find($this->getUser()->getId());
        $userName = $user->getPrenom() . " " . $user->getNom()[0] . ".";
        $criteres = [];
        $sorties = $sortieRepository->findAll();
        $sortiesDefaultList = $sortieRepository->findAll();

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
            }

            if ($criteresDateMax instanceof \DateTime) {
                $criteresDateMax = $criteresDateMax->format('Y-m-d');
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

            $sorties = $sortieRepository->findByIsInscrit($criteres);
            //$sorties = $sortieRepository->findByCampus($criteres);
            //$sorties = $sortieRepository->findByDate($criteres);

        } else {


        }




       

        //dump($request);
        dump($sorties);


        return $this->render('sortie/listeSorties.html.twig', [
            'sorties' => $sorties,
            'sortiesDefaultList' => $sortiesDefaultList,
            //'sortiesInscrits' => $sortiesInscrits,
            'user' => $user,
            'userName' => $userName,
            'criteres' => $criteres,
            'listSortieType' => $listSortieType->createView()
        ]);
    }
}

