<?php

namespace App\Controller;




use DateTime;

class ServiceSortie
{
    function isEditable($sortie, $participant)
    {
        $isOld = in_array($sortie->getEtat()->getId(), [1, 2, 3, 4, 5]);
        $isNotOwned = $sortie->getOrganisateur() != $participant;
        return !$isOld && !$isNotOwned;
    }

    function setEtat($sortie, $status, $repository) {
        if ($status === 1 || $status === 6) {
            $sortie->setEtat($repository->find($status));
        } else {
            $beginning = $sortie->getDateHeureDebut()->getTimestamp();
            $ending = $beginning + ($sortie->getDuree() * 1000 * 60);
            $closing = $sortie->getDateLimiteInscription()->getTimestamp();
            $actual = (new DateTime())->getTimestamp();
            if ($ending < $actual) { $sortie->setEtat($repository->find(5)); }
            else if ($beginning < $actual && $ending > $actual) { $sortie->setEtat($repository->find(4)); }
            else if ($closing < $actual) { $sortie->setEtat($repository->find(3)); }
            else { $sortie->setEtat($repository->find(2)); }
        }
    }

}