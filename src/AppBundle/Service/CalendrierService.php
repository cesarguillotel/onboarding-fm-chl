<?php

namespace AppBundle\Service;

use AppBundle\Entity\Commande;
use AppBundle\Entity\Creneau;
use AppBundle\Repository\CreneauRepository;
use Doctrine\ORM\EntityManager;

class CalendrierService
{
    public const NB_JOURS_APRES_COMMANDE = 2;
    public const NB_JOURS_CALENDRIER = 6;
    public const QUANTITE_MIN = 500;
    public const QUANTITE_MAX = 10000;
    public const ERREUR_QUANTITE_MIN_MAX = 'Veuillez saisir entre 500 et 10000 Litres';

    /** @var EntityManager  */
    private $entityManager;

    /** @var CreneauRepository  */
    private $creneauRepository;

    /** @var DateService  */
    private $dateService;

    public function __construct(EntityManager $entityManager, CreneauRepository $creneauRepository, DateService $dateService)
    {
        $this->entityManager = $entityManager;
        $this->creneauRepository = $creneauRepository;
        $this->dateService = $dateService;
    }

    public function generer($postalCode, int $quantite, \DateTime $dateCommande = null) {

        if (null === $dateCommande) {
            $dateCommande = new \DateTime();
        }

        $joursOuvres = $this->getProchainsJoursOuvres($dateCommande);

        $calendrier = [];

        foreach ($joursOuvres as $jour) {

            $creneauAndRestCapacity = $this->creneauRepository->findWithRestCapacity($jour, $postalCode, $quantite);

            if (!empty($creneauAndRestCapacity)) {

                /** @var Creneau $creneau */
                $creneau = $creneauAndRestCapacity[0];
                $restCapacity = $creneauAndRestCapacity['restCapacity'];

                $calendrier[] = [
                    'date' => $creneau->getDate()->format('Y-m-d'),
                    'truck' => $creneau->getTruck(),
                    'capacity' => $creneau->getCapacity(),
                    'postalCode' => $creneau->getPostalCode(),
                    'id' => $creneau->getId(),
                    'restCapacity' => $restCapacity,
                ];
            }
            else {
                $calendrier[] = [
                    'date' => $jour->format('Y-m-d'),
                ];
            }
        }

        return $calendrier;
    }

    /**
     * @throws \Exception
     */
    public function checkCommand(int $creneauId, int $quantite): Creneau {

        if ($quantite < self::QUANTITE_MIN || $quantite > self::QUANTITE_MAX) {
            throw new \Exception(self::ERREUR_QUANTITE_MIN_MAX);
        }

        $creneauAndRestCapacity = $this->creneauRepository->findByIdWithRestCapacity($creneauId, $quantite);

        if (empty($creneauAndRestCapacity)) {
            throw new \Exception('Créneau non disponible pour cette quantité.');
        }

        $dateCommande = new \DateTime();
        $joursOuvres = $this->getProchainsJoursOuvres($dateCommande);

        /** @var Creneau $creneau */
        $creneau = $creneauAndRestCapacity[0];

        if (!\in_array($creneau->getDate(), $joursOuvres)) {
            throw new \Exception('Date non disponible.');
        }

        return $creneau;
    }

    /**
     * @throws \Exception
     */
    public function commander(int $creneauId, int $quantite): void {

        $creneau = $this->checkCommand($creneauId, $quantite);
        $this->insertCommande($creneau, $quantite);
    }

    private function insertCommande(Creneau $creneau, int $quantite) {

        $commande = new Commande();
        $commande->setQuantite($quantite);
        $commande->setCreneau($creneau);
        $commande->setDate(new \DateTime());

        $this->entityManager->persist($commande);
        $this->entityManager->flush();
    }

    private function getProchainsJoursOuvres(\DateTime $dateCommande): array {

        $jours = [];
        $date = new \DateTime($dateCommande->format('Y-m-d'));
        $date->modify('+' . (self::NB_JOURS_APRES_COMMANDE - 1) . ' day');

        for ($i = 0 ; $i < self::NB_JOURS_CALENDRIER ; ) {

            $date->modify('+1 day');

            if ($this->dateService->estNonTravaille($date)) {
                continue;
            }

            $jours[] = $date;
            $date = clone $date;
            $i++;
        }

        return $jours;
    }
}