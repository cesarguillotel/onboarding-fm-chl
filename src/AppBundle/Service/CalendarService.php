<?php

namespace AppBundle\Service;

use AppBundle\Entity\TruckDay;
use AppBundle\Manager\CommandManager;
use AppBundle\Manager\TruckDayManager;

class CalendarService
{
    public const NB_JOURS_APRES_COMMANDE = 2;
    public const NB_JOURS_CALENDAR = 6;
    public const QUANTITE_MIN = 500;
    public const QUANTITE_MAX = 10000;
    public const ERREUR_QUANTITE_MIN_MAX = 'Veuillez saisir entre 500 et 10000 Litres';

    /** @var CommandManager */
    private $commandManager;

    /** @var TruckDayManager */
    private $truckDayManager;

    /** @var DateService */
    private $dateService;

    public function __construct(TruckDayManager $truckDayManager, CommandManager $commandManager, DateService $dateService)
    {
        $this->commandManager = $commandManager;
        $this->truckDayManager = $truckDayManager;
        $this->dateService = $dateService;
    }

    public function generer($postalCode, int $quantite, \DateTime $dateCommande = null): array
    {
        if (null === $dateCommande) {
            $dateCommande = new \DateTime();
        }

        $joursOuvres = $this->getProchainsJoursOuvres($dateCommande);

        $calendar = [];

        foreach ($joursOuvres as $jour) {
            /** @var TruckDay $truckDay */
            $truckDay = $this->truckDayManager->getTruckDayWithRestCapacity(null, $jour, $postalCode, $quantite);

            if (null !== $truckDay) {
                $calendar[] = $truckDay->toArray();
            } else {
                $calendar[] = [
                    'date' => $jour->format('Y-m-d'),
                ];
            }
        }

        return $calendar;
    }

    /**
     * @throws \Exception
     */
    public function checkCommand(int $truckDayId, int $quantite): TruckDay
    {
        if ($quantite < self::QUANTITE_MIN || $quantite > self::QUANTITE_MAX) {
            throw new \Exception(self::ERREUR_QUANTITE_MIN_MAX);
        }

        /** @var TruckDay $truckDay */
        $truckDay = $this->truckDayManager->getTruckDayWithRestCapacity($truckDayId, null, null, $quantite);

        if (null === $truckDay) {
            throw new \Exception('Créneau non disponible pour cette quantité.');
        }

        $dateCommande = new \DateTime();
        $joursOuvres = $this->getProchainsJoursOuvres($dateCommande);

        if (!\in_array($truckDay->getDate(), $joursOuvres)) {
            throw new \Exception('Date non disponible.');
        }

        return $truckDay;
    }

    /**
     * @throws \Exception
     */
    public function commander(int $truckDayId, int $quantity): void
    {
        $truckDay = $this->checkCommand($truckDayId, $quantity);
        $this->commandManager->insertCommand($truckDay, $quantity);
    }

    private function getProchainsJoursOuvres(\DateTime $dateCommande): array
    {
        $jours = [];
        $date = new \DateTime($dateCommande->format('Y-m-d'));
        $date->modify('+'.(self::NB_JOURS_APRES_COMMANDE - 1).' day');

        $annees = [$date->format('Y')];
        if ('12' === $date->format('m')) {
            $annees[] = ((int) $annees[0]) + 1;
        }

        $this->dateService->initJoursFeries($annees);

        $i = 0;
        while ($i < self::NB_JOURS_CALENDAR) {
            $date->modify('+1 day');

            if ($this->dateService->estNonTravaille($date)) {
                continue;
            }

            $jours[] = $date;
            $date = clone $date;
            ++$i;
        }

        return $jours;
    }
}
