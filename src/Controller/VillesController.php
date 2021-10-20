<?php

namespace App\Controller;

use App\Entity\Ville;

use App\Form\VillesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VillesController extends AbstractController
{
    private $villesListe = null;

    /**
     * @Route("/villes", name="villes")
     */
    public function list(Request $request, EntityManagerInterface $em)
    {
        $this->villesListe = $em->getRepository(Ville::class)->findAll();

        return $this->render('villes/index.html.twig', [
            'page_name' => 'Villes',
            'villes' => $this-> villesListe
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
            $this->addFlash('success', 'La ville a bien été ajoutée !');
            return $this->redirectToRoute('villes');
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
            $this->addFlash('success', 'La ville a bien été modifiée !');

            $this->villesListe = $em->getRepository(Ville::class)->findAll();

            return $this->redirectToRoute('villes');
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
        $this->addFlash('success', 'La ville a été supprimée.');

        return $this->redirectToRoute('villes');
    }


}