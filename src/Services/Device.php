<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class Device
{
    //----------------------------------------------------
    //Fonction qui recherche si le matériel utilisé est un ordi ou un telephone
    //---------------------------------------------------------
    //A utiliser en injection de dépendance dans une fonction
    /*
     public function test(Device $device) {
        $retour = $device->isMobile($request);
     }
     */

    function isMobile(Request $request):bool
    {
        //Recupere le client hint dans le Header
        $useragent = $request->headers->get('sec-ch-ua-mobile');
        //Check si OK
        if (!$useragent) {
            //$this->addFlash('error', "Le device n'est pas reconnu");
            return false;
            //return $this->render('error/error.html.twig');
        } //si la valeur du CH est 0 == Ordinateur
        elseif ($useragent === "?0") {
            return false;
            //$this->addFlash('success', "Le device est un ordi");
            //return $this->render('error/error.html.twig');
        } //si la valeur du CH est 1 == Mobile
        else {
            return true;
            //$this->addFlash('success', "Le device est un téléphone");
            //return $this->render('error/error.html.twig');
        }
    }
}