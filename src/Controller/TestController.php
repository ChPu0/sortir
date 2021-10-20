<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test_index")
     */
    public function index(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('test/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    /**
     * @Route("/sortie/add/{id}", name="sortie_add_participant")
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
                'test_index'
                #TODO=Route à définir
            );
            //Si la date d'inscription est dépassée
        } else if($today >= $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', "Impossible de s'inscrire, la date d'inscription est dépassée");
            return $this->redirectToRoute(
                'test_index'
            #TODO=Route à définir
            );
        }

        //On récupère le participant qui souhaite s'inscrire
        $participant = $this->getUser();
        $participant = $participantRepository->find($participant->getId());

        //On vérifie s'il est déjà inscrit
        if($sortie->getInscrits()->contains($participant)){
            $this->addFlash('error', "Vous êtes déjà inscrits à cette sortie");
            return $this->redirectToRoute(
                'test_index'
            #TODO=Route à définir
            );
            //S'il n'est pas inscrit, on l'ajoute à la sortie
        } else {
            $sortie->addInscrit($participant);
            $participant->addSorty($sortie);
            $entityManager->flush();

            $this->addFlash('success', "Vous êtes inscrits à la sortie " . $sortie->getNom() . "!");
            return $this->redirectToRoute(
                'test_index'
            #TODO=Route à définir
            );
        }
    }
}
