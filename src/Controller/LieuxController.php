<?php

namespace App\Controller;

use App\Entity\Lieu;

use App\Form\LieuType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuxController extends AbstractController
{
    private $lieuxListe = null;

    /**
     * @Route("/lieux", name="lieux")
     */
    public function list(Request $request, EntityManagerInterface $em):Response
    {
        $this->lieuxListe = $em->getRepository(Lieu::class)->findAll();

        return $this->render('lieux/index.html.twig', [
            'page_name' => 'Lieux',
            'lieux' => $this-> lieuxListe
        ]);
    }

    /**
     * @Route("/lieu/add" , name="add_lieu")
     */
    public function add(Request $request, EntityManagerInterface $em){
        $lieu = new Lieu();

        $form = $this->createForm(LieuType::class, $lieu);
        $form -> handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieux = $form->getData();
            $em->persist($lieux);
            $em->flush();
            $this->addFlash('success', 'Le lieu a été ajouté !');
            return $this->redirectToRoute('add_lieu');
        }

        return $this->render('lieux/add.html.twig', [
            'page_name' => 'Ajouter un lieu',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/lieu/{id}", name="edit_lieu")
     */
    public function edit(Lieu $lieu, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->remove('send');
        $form->add('send',SubmitType::class, [
            'label' => 'Modifier',
            'attr' => [
                'class' => 'btn btn-primary w-100'
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $lieu = $form->getData();

            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Le lieu a été modifié !');

            $this->lieuxListe = $em->getRepository(Lieu::class)->findAll();

            return $this->redirectToRoute('lieux');
        }

        return $this->render('lieux/edit.html.twig', [
            'page_name' => 'Edition Lieu',
            'lieu' => $lieu,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/lieu/delete/{id}", name="delete_lieu" , requirements={"id"="\d+"})
     */
    public function delete(Lieu $lieu, Request $request, EntityManagerInterface $em)
    {
        $lieu = $em->getRepository(Lieu::class)->find($request->get('id'));

        $em->remove($lieu);
        $em->flush();
        $this->addFlash('success', 'Le lieu a été supprimé !');

        return $this->redirectToRoute('lieux');
    }
}