<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Main extends AbstractController
{
    /**
     * @Route("/home", name="main_home")
     */
    public function home():Response
    {
        return $this->render('home.html.twig', [
            'page_name' => 'Home'
        ]);
    }

}