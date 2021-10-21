<?php

namespace App\Controller;

use App\Entity\Ville;

use App\Form\SearchType;
use App\Form\VillesType;
use App\Form\VilleSearchType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VillesController extends AbstractController
{
    private $villesListe = null;

    /**
     * @Route("/villes", name="list_villes")
     */
    public function list(Request $request, EntityManagerInterface $em, VilleRepository $villeRepository)
    {


        $searchForm = $this->createForm(VilleSearchType::class);

        if($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()) {
            $critere = $searchForm['nom']->getData();

            $resultat = $villeRepository->findByName($critere);


        }
        else {
            $resultat = $em->getRepository(Ville::class)->findAll();
        }

            return $this->render('villes/list.html.twig', [
            'page_name' => 'Villes',
            'villes' => $resultat,
            'searchForm' => $searchForm->createView()
        ]);
    }


    /**
     * @Route("/ville/add" , name="add_ville")
     */
    public function add(Request $request, EntityManagerInterface $em){
        $ville = new Ville();
        $form = $this->createForm(VillesType::class, $ville);
        $form -> handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ville = $form->getData();
            $em->persist($ville);
            $em->flush();
            $this->addFlash('succes', 'La ville a bien été ajoutée !');
            return $this->redirectToRoute('list_villes');
        }

        return $this->render('villes/add.html.twig', [
            'page_name' => 'Ajout d\'une ville',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/ville/{id}", name="edit_ville")
     */
    public function edit(Ville $ville, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(VillesType::class, $ville);
        $form->remove('submit');
        $form->add('submit',SubmitType::class, [
            'label' => 'Modifier',
            'attr' => [
                'class' => 'btn btn-primary w-100'
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $ville = $form->getData();

            $em->persist($ville);
            $em->flush();
            $this->addFlash('succes', 'La ville a bien été modifiée !');

            $this->villesListe = $em->getRepository(Ville::class)->findAll();

            return $this->redirectToRoute('list_villes');
        }

        return $this->render('villes/edit.html.twig', [
            'page_name' => 'Modification de la ville',
            'ville' => $ville,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/ville/delete/{id}", name="delete_ville" , requirements={"id"="\d+"})
     */
    public function delete(Ville $ville, Request $request, EntityManagerInterface $em)
    {
        $ville = $em->getRepository(Ville::class)->find($request->get('id'));

        $em->remove($ville);
        $em->flush();
        $this->addFlash('succes', 'La ville a été supprimée.');

        return $this->redirectToRoute('list_villes');
    }


}