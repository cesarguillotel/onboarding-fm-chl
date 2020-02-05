<?php

namespace AppBundle\Service;

class DateService
{
    private $joursFeries;

    public function __construct()
    {
        $this->joursFeries = [];
    }

    public function initJoursFeries(array $annees, bool $alsacemoselle = false): void
    {
        $this->joursFeries = [];

        foreach ($annees as $annee) {
            $this->ajouterJoursFeries($annee, $alsacemoselle);
        }
    }

    private function ajouterJoursFeries($annee, $alsacemoselle = false): void
    {
        $this->joursFeries[] = $this->dimanchePaques($annee);
        $this->joursFeries[] = $this->lundiPaques($annee);
        $this->joursFeries[] = $this->jeudiAscension($annee);
        $this->joursFeries[] = $this->lundiPentecote($annee);
        $this->joursFeries[] = "$annee-01-01";
        $this->joursFeries[] = "$annee-05-01";
        $this->joursFeries[] = "$annee-05-08";
        $this->joursFeries[] = "$annee-05-15";
        $this->joursFeries[] = "$annee-07-14";
        $this->joursFeries[] = "$annee-11-11";
        $this->joursFeries[] = "$annee-11-01";
        $this->joursFeries[] = "$annee-12-25";

        if ($alsacemoselle) {
            $this->joursFeries[] = "$annee-12-26";
            $this->joursFeries[] = $this->vendrediSaint($annee);
        }
    }

    public function estFerie(\DateTime $date, $alsacemoselle = false): bool
    {
        $jour = $date->format('Y-m-d');

        if (empty($this->joursFeries)) {
            $annee = $date->format('Y');
            $this->ajouterJoursFeries($annee, $alsacemoselle);
        }

        return in_array($jour, $this->joursFeries, true);
    }

    public function estWeekend(\DateTime $date): bool
    {
        $jour = (int) $date->format('w');

        return 0 === $jour || 6 === $jour;
    }

    public function estNonTravaille(\DateTime $dateTime): bool
    {
        return $this->estWeekend($dateTime) || $this->estFerie($dateTime);
    }

    private function dimanchePaques($annee): string
    {
        return date('Y-m-d', easter_date($annee));
    }

    private function vendrediSaint($annee): string
    {
        $dimanche_paques = $this->dimanchePaques($annee);

        return date('Y-m-d', strtotime("$dimanche_paques -2 day"));
    }

    private function lundiPaques($annee): string
    {
        $dimanche_paques = $this->dimanchePaques($annee);

        return date('Y-m-d', strtotime("$dimanche_paques +1 day"));
    }

    private function jeudiAscension($annee): string
    {
        $dimanche_paques = $this->dimanchePaques($annee);

        return date('Y-m-d', strtotime("$dimanche_paques +39 day"));
    }

    private function lundiPentecote($annee): string
    {
        $dimanche_paques = $this->dimanchePaques($annee);

        return date('Y-m-d', strtotime("$dimanche_paques +50 day"));
    }
}
