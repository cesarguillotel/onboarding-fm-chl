<?php

namespace AppBundle\Service;

use AppBundle\Entity\Creneau;
use AppBundle\Repository\CreneauRepository;
use Doctrine\ORM\EntityManager;

class DateService
{
    public function __construct()
    {

    }

    public function joursFeries($annee, $alsacemoselle = false)
    {
        $joursFeries = [
            $this->dimanchePaques($annee)
            ,    $this->lundiPaques($annee)
            ,    $this->jeudiAscension($annee)
            ,    $this->lundiPentecote($annee)

            ,    "$annee-01-01"        //    Nouvel an
            ,    "$annee-05-01"        //    Fête du travail
            ,    "$annee-05-08"        //    Armistice 1945
            ,    "$annee-05-15"        //    Assomption
            ,    "$annee-07-14"        //    Fête nationale
            ,    "$annee-11-11"        //    Armistice 1918
            ,    "$annee-11-01"        //    Toussaint
            ,    "$annee-12-25"        //    Noël
        ];

        if ($alsacemoselle) {
            $joursFeries[] = "$annee-12-26";
            $joursFeries[] = $this->vendrediSaint($annee);
        }

        sort($joursFeries);
        return $joursFeries;
    }

    public function estFerie(\DateTime $date, $alsacemoselle = false)
    {
        $jour = $date->format('Y-m-d');
        $annee = $date->format('Y');
        return in_array($jour, $this->joursFeries($annee, $alsacemoselle));
    }

    public function estWeekend(\DateTime $date)
    {
        $jour = (int)$date->format('w');
        return $jour === 0 || $jour === 6;
    }

    public function estNonTravaille(\DateTime $dateTime) {
        return $this->estWeekend($dateTime) || $this->estFerie($dateTime);
    }

    private function dimanchePaques($annee)
    {
        return date("Y-m-d", easter_date($annee));
    }

    private function vendrediSaint($annee)
    {
        $dimanche_paques = $this->dimanchePaques($annee);
        return date("Y-m-d", strtotime("$dimanche_paques -2 day"));
    }

    private function lundiPaques($annee)
    {
        $dimanche_paques = $this->dimanchePaques($annee);
        return date("Y-m-d", strtotime("$dimanche_paques +1 day"));
    }

    private function jeudiAscension($annee)
    {
        $dimanche_paques = $this->dimanchePaques($annee);
        return date("Y-m-d", strtotime("$dimanche_paques +39 day"));
    }

    private function lundiPentecote($annee)
    {
        $dimanche_paques = $this->dimanchePaques($annee);
        return date("Y-m-d", strtotime("$dimanche_paques +50 day"));
    }


}